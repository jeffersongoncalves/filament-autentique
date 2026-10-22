<?php

namespace JeffersonGoncalves\Filament\Autentique\Resources\DocumentResource\Pages;

use Filament\Resources\Pages\ViewRecord;
use JeffersonGoncalves\Filament\Autentique\Resources\DocumentResource;
use JeffersonGoncalves\Filament\Autentique\Resources\DocumentResource\DocumentActions;

class ViewDocument extends ViewRecord
{
    protected static string $resource = DocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DocumentActions::download(),
            DocumentActions::sync(),
            DocumentActions::delete(),
        ];
    }
}
