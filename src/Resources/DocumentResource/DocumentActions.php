<?php

namespace JeffersonGoncalves\Filament\Autentique\Resources\DocumentResource;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use JeffersonGoncalves\Autentique\Exceptions\AutentiqueException;
use JeffersonGoncalves\Autentique\Facades\Autentique;
use JeffersonGoncalves\Autentique\Jobs\CreateDocument;
use JeffersonGoncalves\Autentique\Jobs\SyncDocument;
use JeffersonGoncalves\Autentique\Models\Document;
use JeffersonGoncalves\Filament\Autentique\AutentiquePlugin;
use JeffersonGoncalves\Filament\Autentique\Resources\DocumentResource;

/**
 * Actions shared by the documents table and the view page.
 */
class DocumentActions
{
    /** Upload a file and queue it to Autentique (CreateDocument job). */
    public static function send(): Action
    {
        $plugin = AutentiquePlugin::current();

        return Action::make('send')
            ->label(__('filament-autentique::autentique.actions.send'))
            ->icon('heroicon-o-paper-airplane')
            ->modalWidth('3xl')
            ->schema([
                TextInput::make('name')->label(DocumentResource::field('name'))->required()->maxLength(255),
                FileUpload::make('file')
                    ->label(DocumentResource::field('file'))
                    ->required()
                    ->disk($plugin->getDisk())
                    ->directory($plugin->getDirectory())
                    ->visibility('private')
                    ->maxSize(20480),
                Textarea::make('message')->label(DocumentResource::field('message'))->rows(2),
                Repeater::make('signers')
                    ->label(DocumentResource::field('signers'))
                    ->required()
                    ->minItems(1)
                    ->defaultItems(1)
                    ->columns(3)
                    ->schema([
                        TextInput::make('email')->label(DocumentResource::field('email'))->email()->requiredWithout('name'),
                        TextInput::make('name')->label(DocumentResource::field('signer_name')),
                        Select::make('action')
                            ->label(DocumentResource::field('action'))
                            ->options(static::signerActions())
                            ->default('SIGN')
                            ->required(),
                    ]),
                Toggle::make('refusable')->label(DocumentResource::field('refusable'))->default(true),
                Toggle::make('sortable')->label(DocumentResource::field('sortable')),
            ])
            ->action(function (array $data) use ($plugin): void {
                CreateDocument::dispatch(
                    array_filter([
                        'name' => $data['name'],
                        'message' => $data['message'] ?? null,
                        'refusable' => (bool) ($data['refusable'] ?? false),
                        'sortable' => (bool) ($data['sortable'] ?? false),
                    ], fn ($value) => $value !== null && $value !== ''),
                    collect($data['signers'])
                        ->map(fn (array $signer): array => array_filter([
                            'email' => $signer['email'] ?? null,
                            'name' => $signer['name'] ?? null,
                            'action' => $signer['action'],
                        ], fn ($value) => filled($value)))
                        ->values()
                        ->all(),
                    (string) $data['file'],
                    $plugin->getDisk(),
                );

                Notification::make()->success()->title(__('filament-autentique::autentique.notifications.queued'))->send();
            });
    }

    /** Refresh the document and its signers from the API (SyncDocument job). */
    public static function sync(): Action
    {
        return Action::make('sync')
            ->label(__('filament-autentique::autentique.actions.sync'))
            ->icon('heroicon-o-arrow-path')
            ->color('gray')
            ->action(function (Document $record): void {
                SyncDocument::dispatch($record->autentique_id);

                Notification::make()->success()->title(__('filament-autentique::autentique.notifications.synced'))->send();
            });
    }

    public static function download(): Action
    {
        return Action::make('download')
            ->label(__('filament-autentique::autentique.actions.download'))
            ->icon('heroicon-o-arrow-down-tray')
            ->color('success')
            ->visible(fn (Document $record): bool => $record->isSigned() && filled($record->signed_url))
            ->url(fn (Document $record): ?string => $record->signed_url, shouldOpenInNewTab: true);
    }

    /** Delete at Autentique first; the local row is only removed when the API agrees. */
    public static function delete(): DeleteAction
    {
        return DeleteAction::make()
            ->before(function (Document $record, DeleteAction $action): void {
                try {
                    Autentique::deleteDocument($record->autentique_id);
                } catch (AutentiqueException $e) {
                    static::failed($e);

                    $action->cancel();
                }
            });
    }

    /** @return array<string, string> */
    public static function signerActions(): array
    {
        return collect(['SIGN', 'SIGN_AS_A_WITNESS', 'APPROVE', 'RECOGNIZE'])
            ->mapWithKeys(fn (string $action): array => [$action => __("filament-autentique::autentique.signer_actions.{$action}")])
            ->all();
    }

    public static function failed(AutentiqueException $e): void
    {
        Notification::make()
            ->danger()
            ->title(__('filament-autentique::autentique.notifications.failed'))
            ->body($e->getMessage())
            ->send();
    }
}
