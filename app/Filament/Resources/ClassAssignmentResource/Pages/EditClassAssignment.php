<?php

namespace App\Filament\Resources\ClassAssignmentResource\Pages;

use App\Filament\Resources\ClassAssignmentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditClassAssignment extends EditRecord
{
    protected static string $resource = ClassAssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
