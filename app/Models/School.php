<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class School extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'address',
        'city',
        'phone',
        'email',
    ];

    /**
     * Get the classes for the school.
     */
    public function classRooms(): HasMany
    {
        return $this->hasMany(ClassRoom::class);
    }
}
