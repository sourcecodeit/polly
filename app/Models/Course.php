<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'credits',
    ];

    /**
     * Get the lessons for the course.
     */
    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class);
    }

    /**
     * Get the class assignments for the course.
     */
    public function classAssignments(): HasMany
    {
        return $this->hasMany(ClassAssignment::class);
    }

    /**
     * Get the votes for the course.
     */
    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }
}
