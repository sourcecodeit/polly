<?php

namespace App\Filament\GlobalSearch;

use App\Filament\Resources\StudentResource;
use App\Models\Student;
use Filament\GlobalSearch\GlobalSearchProvider;
use Filament\GlobalSearch\GlobalSearchResult;
use Filament\GlobalSearch\GlobalSearchResults;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class StudentGlobalSearchProvider extends GlobalSearchProvider
{
    protected function getResults(string $query): GlobalSearchResults
    {
        $results = new GlobalSearchResults();

        // Search for students
        $students = Student::query()
            ->where(function (Builder $builder) use ($query) {
                $builder
                    ->where('first_name', 'like', "%{$query}%")
                    ->orWhere('last_name', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%")
                    ->orWhere('phone', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get();

        // If only one student is found, redirect to the student's edit page
        if ($students->count() === 1) {
            $student = $students->first();
            
            return $results->redirect(
                StudentResource::getUrl('edit', ['record' => $student])
            );
        }

        // Otherwise, add all students to the results
        foreach ($students as $student) {
            $results->addResult(
                new GlobalSearchResult(
                    title: $student->full_name,
                    url: StudentResource::getUrl('edit', ['record' => $student]),
                    details: [
                        'Class' => $student->classRoom?->name ?? 'No Class',
                        'Email' => $student->email,
                    ],
                    actions: [],
                )
            );
        }

        return $results;
    }
}
