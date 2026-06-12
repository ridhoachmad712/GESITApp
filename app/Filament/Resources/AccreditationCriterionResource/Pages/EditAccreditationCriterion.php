<?php

namespace App\Filament\Resources\AccreditationCriterionResource\Pages;

use App\Filament\Resources\AccreditationCriterionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAccreditationCriterion extends EditRecord
{
    protected static string $resource = AccreditationCriterionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
