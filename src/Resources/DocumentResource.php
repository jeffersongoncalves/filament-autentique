<?php

namespace JeffersonGoncalves\Filament\Autentique\Resources;

use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use JeffersonGoncalves\Autentique\Models\Document;
use JeffersonGoncalves\Filament\Autentique\AutentiquePlugin;
use JeffersonGoncalves\Filament\Autentique\Resources\DocumentResource\DocumentActions;
use JeffersonGoncalves\Filament\Autentique\Resources\DocumentResource\Pages\ListDocuments;
use JeffersonGoncalves\Filament\Autentique\Resources\DocumentResource\Pages\ViewDocument;
use JeffersonGoncalves\Filament\Autentique\Resources\DocumentResource\RelationManagers\SignaturesRelationManager;

class DocumentResource extends Resource
{
    protected static ?string $model = Document::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-check';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $slug = 'autentique-documents';

    public static function getNavigationGroup(): ?string
    {
        return AutentiquePlugin::current()->getNavigationGroup();
    }

    public static function getModelLabel(): string
    {
        return __('filament-autentique::autentique.document.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament-autentique::autentique.document.plural_label');
    }

    public static function canCreate(): bool
    {
        // Documents are sent with the "Send document" action (queued upload), not a create form.
        return false;
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()
                ->columns(3)
                ->columnSpanFull()
                ->schema([
                    TextEntry::make('name')->label(static::field('name')),
                    TextEntry::make('status')
                        ->label(static::field('status'))
                        ->badge()
                        ->formatStateUsing(fn (string $state): string => static::statusLabel($state))
                        ->color(fn (string $state): string => static::statusColor($state)),
                    TextEntry::make('signed_count')
                        ->label(static::field('signatures'))
                        ->formatStateUsing(fn (Document $record): string => "{$record->signed_count}/{$record->signatures_count}"),
                    TextEntry::make('autentique_id')->label(static::field('autentique_id'))->copyable(),
                    TextEntry::make('documentable_type')
                        ->label(static::field('documentable'))
                        ->formatStateUsing(fn (Document $record): string => class_basename((string) $record->documentable_type).' #'.$record->documentable_id)
                        ->placeholder('-'),
                    IconEntry::make('sandbox')->label(static::field('sandbox'))->boolean(),
                    TextEntry::make('created_at')->label(static::field('created_at'))->dateTime(),
                    TextEntry::make('finished_at')->label(static::field('finished_at'))->dateTime()->placeholder('-'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')->label(static::field('name'))->searchable()->sortable(),
                TextColumn::make('status')
                    ->label(static::field('status'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => static::statusLabel($state))
                    ->color(fn (string $state): string => static::statusColor($state)),
                TextColumn::make('signed_count')
                    ->label(static::field('signatures'))
                    ->formatStateUsing(fn (Document $record): string => "{$record->signed_count}/{$record->signatures_count}"),
                IconColumn::make('sandbox')->label(static::field('sandbox'))->boolean()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')->label(static::field('created_at'))->dateTime()->sortable(),
                TextColumn::make('finished_at')->label(static::field('finished_at'))->dateTime()->sortable()->placeholder('-'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(static::field('status'))
                    ->options(collect([Document::STATUS_PENDING, Document::STATUS_SIGNED, Document::STATUS_REJECTED])
                        ->mapWithKeys(fn (string $status): array => [$status => static::statusLabel($status)])
                        ->all()),
                TernaryFilter::make('sandbox')->label(static::field('sandbox')),
            ])
            ->recordActions([
                ViewAction::make(),
                DocumentActions::download(),
                DocumentActions::sync(),
                DocumentActions::delete(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            SignaturesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDocuments::route('/'),
            'view' => ViewDocument::route('/{record}'),
        ];
    }

    public static function field(string $name): string
    {
        return __("filament-autentique::autentique.document.fields.{$name}");
    }

    public static function statusLabel(string $status): string
    {
        return __("filament-autentique::autentique.document.status.{$status}");
    }

    public static function statusColor(string $status): string
    {
        return match ($status) {
            Document::STATUS_SIGNED => 'success',
            Document::STATUS_REJECTED => 'danger',
            default => 'warning',
        };
    }
}
