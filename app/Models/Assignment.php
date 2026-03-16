<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    use HasFactory;

    // Eager load course and room relationships by default
    protected $with = ['course', 'room'];

    protected $fillable = [
        'course_id',
        'room_id',
        'date',
        'period',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}
