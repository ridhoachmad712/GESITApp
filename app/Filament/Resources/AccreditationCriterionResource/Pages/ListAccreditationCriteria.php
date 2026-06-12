<?php

namespace App\Filament\Resources\AccreditationCriterionResource\Pages;

use App\Filament\Resources\AccreditationCriterionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAccreditationCriteria extends ListRecords
{
    protected static string $resource = AccreditationCriterionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
