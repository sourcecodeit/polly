<?php

namespace App\Filament\Widgets;

use App\Models\Student;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Model;

class StudentViewStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;
    
    public ?Model $record = null;

    protected function getStats(): array
    {
        if (!$this->record instanceof Student) {
            return [];
        }
        
        $record = $this->record;

        $student = $record;

        // Calculate attendance statistics
        $totalAttendances = $student->attendances()->count();
        $presentCount = $student->attendances()->where('status', 'present')->count();
        $absentCount = $student->attendances()->where('status', 'absent')->count();
        $lateCount = $student->attendances()->where('status', 'late')->count();
        
        $attendanceRate = $totalAttendances > 0 
            ? round(($presentCount / $totalAttendances) * 100, 1) 
            : 0;

        // Calculate average vote
        $averageVote = $student->votes()->avg('value') ?? 'N/A';
        if (is_numeric($averageVote)) {
            $averageVote = number_format($averageVote, 1);
        }

        // Count notes
        $positiveNotes = $student->notes()->where('type', 'positive')->count();
        $negativeNotes = $student->notes()->where('type', 'negative')->count();

        return [
            Stat::make('Attendance Rate', $attendanceRate . '%')
                ->description($presentCount . ' present, ' . $absentCount . ' absent, ' . $lateCount . ' late')
                ->descriptionIcon('heroicon-m-calendar')
                ->color($attendanceRate >= 90 ? 'success' : ($attendanceRate >= 75 ? 'warning' : 'danger')),

            Stat::make('Average Vote', $averageVote)
                ->description('From ' . $student->votes()->count() . ' votes')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color(is_numeric($averageVote) && $averageVote >= 7 ? 'success' : (is_numeric($averageVote) && $averageVote >= 6 ? 'warning' : 'danger')),

            Stat::make('Notes', $positiveNotes . ' positive, ' . $negativeNotes . ' negative')
                ->description('Total: ' . ($positiveNotes + $negativeNotes))
                ->descriptionIcon('heroicon-m-document-text')
                ->color($positiveNotes > $negativeNotes ? 'success' : ($positiveNotes == $negativeNotes ? 'warning' : 'danger')),
        ];
    }
}
