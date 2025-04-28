<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentResource\Pages;
use App\Filament\Resources\StudentResource\RelationManagers;
use App\Models\Student;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'School Management';

    protected static ?string $recordTitleAttribute = 'full_name';

    public static function getGloballySearchableAttributes(): array
    {
        return ['first_name', 'last_name', 'email', 'phone'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->full_name;
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Class' => $record->classRoom?->name ?? 'No Class',
            'Email' => $record->email,
        ];
    }

    public static function getGlobalSearchResultUrl(Model $record): string
    {
        return static::getUrl('view', ['record' => $record]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Student Information')
                    ->schema([
                        Forms\Components\Select::make('class_id')
                            ->relationship('classRoom', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('first_name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('last_name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('birth_date'),
                        Forms\Components\Select::make('gender')
                            ->options([
                                'male' => 'Male',
                                'female' => 'Female',
                                'other' => 'Other',
                            ]),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('address')
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ]),
                Forms\Components\Section::make('Parent Information')
                    ->schema([
                        Forms\Components\TextInput::make('parent_name')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('parent_phone')
                            ->tel()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('parent_email')
                            ->email()
                            ->maxLength(255),
                    ]),
                Forms\Components\Textarea::make('notes')
                    ->maxLength(65535)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('first_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('absences_count')
                    ->label('Absences')
                    ->getStateUsing(function ($record) {
                        return $record->attendances()->where('status', 'absent')->count();
                    })
                    ->sortable(query: function ($query, $direction) {
                        return $query
                            ->withCount(['attendances as absences_count' => function ($query) {
                                $query->where('status', 'absent');
                            }])
                            ->orderBy('absences_count', $direction);
                    }),
                Tables\Columns\TextColumn::make('positive_notes_count')
                    ->label('Positive Notes')
                    ->getStateUsing(function ($record) {
                        $count = $record->notes()->where('type', 'positive')->count();
                        return $count > 0 ? $count : '';
                    })
                    ->badge()
                    ->color('success')
                    ->sortable(query: function ($query, $direction) {
                        return $query
                            ->withCount(['notes as positive_notes_count' => function ($query) {
                                $query->where('type', 'positive');
                            }])
                            ->orderBy('positive_notes_count', $direction);
                    }),
                Tables\Columns\TextColumn::make('negative_notes_count')
                    ->label('Negative Notes')
                    ->getStateUsing(function ($record) {
                        $count = $record->notes()->where('type', 'negative')->count();
                        return $count > 0 ? $count : '';
                    })
                    ->badge()
                    ->color('danger')
                    ->sortable(query: function ($query, $direction) {
                        return $query
                            ->withCount(['notes as negative_notes_count' => function ($query) {
                                $query->where('type', 'negative');
                            }])
                            ->orderBy('negative_notes_count', $direction);
                    }),
                Tables\Columns\TextColumn::make('average_vote')
                    ->label('Average Vote')
                    ->getStateUsing(function ($record) {
                        return $record->votes()->avg('value') ?? 'N/A';
                    })
                    ->sortable(query: function ($query, $direction) {
                        return $query
                            ->leftJoin('votes', 'students.id', '=', 'votes.student_id')
                            ->selectRaw('students.*, AVG(votes.value) as avg_vote')
                            ->groupBy('students.id')
                            ->orderBy('avg_vote', $direction);
                    }),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\AttendancesRelationManager::make(),
            RelationManagers\VotesRelationManager::make(),
            RelationManagers\NotesRelationManager::make(),
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Tabs::make('Student Details')
                    ->tabs([
                        Infolists\Components\Tabs\Tab::make('Personal Information')
                            ->schema([
                                Infolists\Components\Section::make()
                                    ->schema([
                                        Infolists\Components\Grid::make(2)
                                            ->schema([
                                                Infolists\Components\TextEntry::make('full_name')
                                                    ->label('Name'),
                                                Infolists\Components\TextEntry::make('classRoom.name')
                                                    ->label('Class'),
                                                Infolists\Components\TextEntry::make('birth_date')
                                                    ->date('d/m/Y'),
                                                Infolists\Components\TextEntry::make('gender')
                                                    ->formatStateUsing(fn (?string $state): string => $state ? ucfirst($state) : 'Not provided'),
                                                Infolists\Components\TextEntry::make('email'),
                                                Infolists\Components\TextEntry::make('phone'),
                                            ]),
                                        Infolists\Components\TextEntry::make('address')
                                            ->columnSpanFull(),
                                    ]),
                                Infolists\Components\Section::make('Parent Information')
                                    ->schema([
                                        Infolists\Components\Grid::make(2)
                                            ->schema([
                                                Infolists\Components\TextEntry::make('parent_name')
                                                    ->label('Name'),
                                                Infolists\Components\TextEntry::make('parent_email')
                                                    ->label('Email'),
                                                Infolists\Components\TextEntry::make('parent_phone')
                                                    ->label('Phone'),
                                            ]),
                                    ]),
                                Infolists\Components\Section::make('Notes')
                                    ->schema([
                                        Infolists\Components\TextEntry::make('notes')
                                            ->columnSpanFull()
                                            ->markdown(),
                                    ])
                                    ->collapsible()
                                    ->hidden(fn ($record) => empty($record->notes)),
                            ]),
                        Infolists\Components\Tabs\Tab::make('Attendances')
                            ->schema([
                                Infolists\Components\RepeatableEntry::make('attendances')
                                    ->schema([
                                        Infolists\Components\Grid::make(3)
                                            ->schema([
                                                Infolists\Components\TextEntry::make('lesson.date')
                                                    ->date('d/m/Y')
                                                    ->label('Date'),
                                                Infolists\Components\TextEntry::make('lesson.subject')
                                                    ->label('Subject'),
                                                Infolists\Components\TextEntry::make('status')
                                                    ->badge()
                                                    ->color(fn (string $state): string => match ($state) {
                                                        'present' => 'success',
                                                        'absent' => 'danger',
                                                        'late' => 'warning',
                                                        default => 'gray',
                                                    }),
                                            ]),
                                        Infolists\Components\TextEntry::make('notes')
                                            ->columnSpanFull()
                                            ->visible(fn ($record) => !empty($record->notes)),
                                    ])
                                    ->columnSpanFull(),
                            ])
                            ->badge(fn ($record) => $record->attendances()->count()),
                        Infolists\Components\Tabs\Tab::make('Votes')
                            ->schema([
                                Infolists\Components\RepeatableEntry::make('votes')
                                    ->schema([
                                        Infolists\Components\Grid::make(3)
                                            ->schema([
                                                Infolists\Components\TextEntry::make('date')
                                                    ->date('d/m/Y'),
                                                Infolists\Components\TextEntry::make('subject'),
                                                Infolists\Components\TextEntry::make('value')
                                                    ->badge()
                                                    ->color(fn ($state) => 
                                                        $state >= 7 ? 'success' : 
                                                        ($state >= 6 ? 'warning' : 'danger')
                                                    ),
                                            ]),
                                        Infolists\Components\TextEntry::make('notes')
                                            ->columnSpanFull()
                                            ->visible(fn ($record) => !empty($record->notes)),
                                    ])
                                    ->columnSpanFull(),
                            ])
                            ->badge(fn ($record) => $record->votes()->count()),
                        Infolists\Components\Tabs\Tab::make('Notes')
                            ->schema([
                                Infolists\Components\RepeatableEntry::make('notes')
                                    ->schema([
                                        Infolists\Components\Grid::make(2)
                                            ->schema([
                                                Infolists\Components\TextEntry::make('created_at')
                                                    ->date('d/m/Y')
                                                    ->label('Date'),
                                                Infolists\Components\TextEntry::make('type')
                                                    ->badge()
                                                    ->color(fn (string $state): string => match ($state) {
                                                        'positive' => 'success',
                                                        'negative' => 'danger',
                                                        default => 'gray',
                                                    })
                                                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                                            ]),
                                        Infolists\Components\TextEntry::make('content')
                                            ->columnSpanFull(),
                                    ])
                                    ->columnSpanFull(),
                            ])
                            ->badge(fn ($record) => $record->notes()->count()),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStudents::route('/'),
            'create' => Pages\CreateStudent::route('/create'),
            'view' => Pages\ViewStudent::route('/{record}'),
            'edit' => Pages\EditStudent::route('/{record}/edit'),
        ];
    }
}
