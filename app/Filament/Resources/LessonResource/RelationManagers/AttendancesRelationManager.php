<?php

namespace App\Filament\Resources\LessonResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AttendancesRelationManager extends RelationManager
{
    protected static string $relationship = 'attendances';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('student_id')
                    ->relationship('student', 'first_name', function (Builder $query, $record) {
                        // Only show students from the same class as the lesson
                        if ($record && $record->lesson) {
                            $query->where('class_id', $record->lesson->class_id);
                        }
                    })
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->first_name} {$record->last_name}")
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('status')
                    ->options([
                        'present' => 'Present',
                        'absent' => 'Absent',
                        'late' => 'Late',
                        'excused' => 'Excused',
                    ])
                    ->required()
                    ->default('present'),
                Forms\Components\Textarea::make('notes')
                    ->maxLength(65535)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('student.full_name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'present' => 'success',
                        'absent' => 'danger',
                        'late' => 'warning',
                        'excused' => 'info',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
                Tables\Actions\Action::make('markAllPresent')
                    ->label('Mark All Present')
                    ->color('success')
                    ->icon('heroicon-o-check')
                    ->action(function (RelationManager $livewire) {
                        $classId = $livewire->getOwnerRecord()->class_id;
                        $students = \App\Models\Student::where('class_id', $classId)->get();
                        
                        foreach ($students as $student) {
                            \App\Models\Attendance::updateOrCreate(
                                [
                                    'lesson_id' => $livewire->getOwnerRecord()->id,
                                    'student_id' => $student->id,
                                ],
                                [
                                    'status' => 'present',
                                ]
                            );
                        }
                        
                        // Refresh the table after the action
                        $livewire->resetTable();
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('updateStatus')
                        ->label('Update Status')
                        ->icon('heroicon-o-pencil')
                        ->form([
                            Forms\Components\Select::make('status')
                                ->options([
                                    'present' => 'Present',
                                    'absent' => 'Absent',
                                    'late' => 'Late',
                                    'excused' => 'Excused',
                                ])
                                ->required(),
                        ])
                        ->action(function (RelationManager $livewire, array $data, array $records) {
                            foreach ($records as $record) {
                                $record->update([
                                    'status' => $data['status'],
                                ]);
                            }
                            
                            // Refresh the table after the action
                            $livewire->resetTable();
                        }),
                ]),
            ]);
    }
}
