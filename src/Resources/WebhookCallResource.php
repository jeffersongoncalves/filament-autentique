<?php

namespace JeffersonGoncalves\Filament\Autentique\Resources;

use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use JeffersonGoncalves\Autentique\Jobs\ProcessWebhookCall;
use JeffersonGoncalves\Autentique\Models\WebhookCall;
use JeffersonGoncalves\Filament\Autentique\AutentiquePlugin;
use JeffersonGoncalves\Filament\Autentique\Resources\WebhookCallResource\Pages\ListWebhookCalls;

class WebhookCallResource extends Resource
{
    protected static ?string $model = WebhookCall::class;

    protected static ?string $navigationIcon = 'heroicon-o-bolt';

    protected static ?string $slug = 'autentique-webhook-calls';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return AutentiquePlugin::current()->getNavigationGroup();
    }

    public static function getModelLabel(): string
    {
        return __('filament-autentique::autentique.webhook_call.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament-autentique::autentique.webhook_call.plural_label');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('type')->label(static::field('type'))->badge()->color('gray')->searchable(),
                TextColumn::make('event_id')->label(static::field('event_id'))->copyable()->limit(20)->searchable()->toggleable(),
                IconColumn::make('processed_at')
                    ->label(static::field('processed'))
                    ->state(fn (WebhookCall $record): bool => $record->processed_at !== null)
                    ->boolean(),
                TextColumn::make('exception')
                    ->label(static::field('exception'))
                    ->limit(60)
                    ->tooltip(fn (WebhookCall $record): ?string => $record->exception)
                    ->color('danger')
                    ->placeholder('-'),
                TextColumn::make('created_at')->label(static::field('created_at'))->dateTime()->sortable(),
            ])
            ->filters([
                Filter::make('failed')
                    ->label(__('filament-autentique::autentique.webhook_call.filters.failed'))
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('exception')),
            ])
            ->actions([
                Action::make('payload')
                    ->label(__('filament-autentique::autentique.actions.payload'))
                    ->icon('heroicon-o-code-bracket')
                    ->color('gray')
                    ->modalSubmitAction(false)
                    ->modalContent(fn (WebhookCall $record): HtmlString => new HtmlString(
                        '<pre style="white-space: pre-wrap; font-size: 0.75rem;">'.e((string) json_encode($record->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)).'</pre>'
                    )),
                Action::make('reprocess')
                    ->label(__('filament-autentique::autentique.actions.reprocess'))
                    ->icon('heroicon-o-arrow-path')
                    ->requiresConfirmation()
                    ->action(function (WebhookCall $record): void {
                        $record->update(['exception' => null]);
                        ProcessWebhookCall::dispatch($record);

                        Notification::make()->success()->title(__('filament-autentique::autentique.notifications.reprocessed'))->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWebhookCalls::route('/'),
        ];
    }

    protected static function field(string $name): string
    {
        return __("filament-autentique::autentique.webhook_call.fields.{$name}");
    }
}
