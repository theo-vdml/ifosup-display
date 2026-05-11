<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    use HasFactory;

    protected $with = ['course', 'room'];

    protected $fillable = [
        'course_id',
        'room_id',
        'date',
        'period',
        'status',
        'recurring_assignment_id',
        'is_detached',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function recurringAssignment()
    {
        return $this->belongsTo(RecurringAssignment::class);
    }
}
