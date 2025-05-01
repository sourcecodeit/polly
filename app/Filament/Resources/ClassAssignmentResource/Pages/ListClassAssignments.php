<?php

namespace App\Filament\Resources\ClassAssignmentResource\Pages;

use App\Filament\Resources\ClassAssignmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListClassAssignments extends ListRecords
{
    protected static string $resource = ClassAssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
