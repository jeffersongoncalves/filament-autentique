<?php

namespace JeffersonGoncalves\Filament\Autentique\Resources\DocumentResource\Pages;

use Filament\Resources\Pages\ListRecords;
use JeffersonGoncalves\Filament\Autentique\Resources\DocumentResource;
use JeffersonGoncalves\Filament\Autentique\Resources\DocumentResource\DocumentActions;

class ListDocuments extends ListRecords
{
    protected static string $resource = DocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DocumentActions::send(),
        ];
    }
}
