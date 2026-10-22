<?php

namespace JeffersonGoncalves\Filament\Autentique\Resources\DocumentResource\RelationManagers;

use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use JeffersonGoncalves\Autentique\Exceptions\AutentiqueException;
use JeffersonGoncalves\Autentique\Facades\Autentique;
use JeffersonGoncalves\Autentique\Models\Signature;
use JeffersonGoncalves\Filament\Autentique\Resources\DocumentResource\DocumentActions;

class SignaturesRelationManager extends RelationManager
{
    protected static string $relationship = 'signatures';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('filament-autentique::autentique.signature.plural_label');
    }

    public function isReadOnly(): bool
    {
        // Only the custom actions below are offered; there is no create/edit form.
        return false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('email')
            ->modelLabel(__('filament-autentique::autentique.signature.label'))
            ->pluralModelLabel(__('filament-autentique::autentique.signature.plural_label'))
            ->columns([
                TextColumn::make('name')->label(static::field('name'))->placeholder('-'),
                TextColumn::make('email')->label(static::field('email'))->placeholder('-'),
                TextColumn::make('action')
                    ->label(static::field('action'))
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn (string $state): string => DocumentActions::signerActions()[strtoupper($state)] ?? $state),
                TextColumn::make('link')->label(static::field('link'))->copyable()->limit(30)->placeholder('-'),
                TextColumn::make('viewed_at')->label(static::field('viewed_at'))->dateTime()->placeholder('-'),
                TextColumn::make('signed_at')->label(static::field('signed_at'))->dateTime()->placeholder('-'),
                TextColumn::make('rejected_at')->label(static::field('rejected_at'))->dateTime()->placeholder('-'),
            ])
            ->actions([
                Action::make('resend')
                    ->label(__('filament-autentique::autentique.actions.resend'))
                    ->icon('heroicon-o-envelope')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->visible(fn (Signature $record): bool => static::isOpen($record) && filled($record->email))
                    ->action(function (Signature $record): void {
                        try {
                            Autentique::resendSignatures([$record->public_id]);
                        } catch (AutentiqueException $e) {
                            DocumentActions::failed($e);

                            return;
                        }

                        Notification::make()->success()->title(__('filament-autentique::autentique.notifications.resent'))->send();
                    }),
                Action::make('createLink')
                    ->label(__('filament-autentique::autentique.actions.create_link'))
                    ->icon('heroicon-o-link')
                    ->color('gray')
                    ->visible(fn (Signature $record): bool => static::isOpen($record) && blank($record->link))
                    ->action(function (Signature $record): void {
                        try {
                            $record->update(['link' => Autentique::createLinkToSignature($record->public_id)]);
                        } catch (AutentiqueException $e) {
                            DocumentActions::failed($e);

                            return;
                        }

                        Notification::make()->success()->title(__('filament-autentique::autentique.notifications.link_created'))->body($record->link)->send();
                    }),
            ]);
    }

    protected static function isOpen(Signature $signature): bool
    {
        return $signature->signed_at === null && $signature->rejected_at === null;
    }

    protected static function field(string $name): string
    {
        return __("filament-autentique::autentique.signature.fields.{$name}");
    }
}
