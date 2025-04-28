<?php

namespace App\Filament\Widgets;

use App\Models\Lesson;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class LessonsCalendarWidget extends Widget
{
    protected static string $view = 'filament.widgets.lessons-calendar-widget';

    public ?string $filter = 'today';

    protected int | string | array $columnSpan = 'full';
    
    protected function getColumnSpanClass(): string
    {
        return 'col-span-full';
    }

    protected function getViewData(): array
    {
        $startDate = $this->getStartDate();
        $endDate = $this->getEndDate();

        $lessons = Lesson::query()
            ->whereBetween('start_time', [$startDate, $endDate])
            ->with(['classRoom', 'attendances.student'])
            ->get()
            ->groupBy(function ($lesson) {
                return Carbon::parse($lesson->start_time)->format('Y-m-d');
            });

        $dates = collect();
        for ($date = clone $startDate; $date->lte($endDate); $date->addDay()) {
            $dates->push(clone $date);
        }

        return [
            'lessons' => $lessons,
            'dates' => $dates,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ];
    }

    protected function getStartDate(): Carbon
    {
        return match ($this->filter) {
            'today' => Carbon::today(),
            'yesterday' => Carbon::yesterday(),
            'week' => Carbon::now()->startOfWeek(),
            'month' => Carbon::now()->startOfMonth(),
            default => Carbon::today(),
        };
    }

    protected function getEndDate(): Carbon
    {
        return match ($this->filter) {
            'today' => Carbon::today()->endOfDay(),
            'yesterday' => Carbon::yesterday()->endOfDay(),
            'week' => Carbon::now()->endOfWeek()->endOfDay(),
            'month' => Carbon::now()->endOfMonth()->endOfDay(),
            default => Carbon::today()->endOfDay(),
        };
    }

    protected function getFilters(): array
    {
        return [
            'today' => 'Today',
            'yesterday' => 'Yesterday',
            'week' => 'This week',
            'month' => 'This month',
        ];
    }
}
