<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecurringAssignment extends Model
{
    protected $fillable = [
        'course_id',
        'room_id',
        'day_of_week',
        'period',
        'start_date',
        'end_date',
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
