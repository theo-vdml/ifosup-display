<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Room;
use Inertia\Inertia;

class ScheduleController extends Controller
{
    public function index()
    {
        $assignments = Assignment::all();
        $rooms = Room::all();

        return Inertia::render('Schedule', [
            'assignments' => $assignments,
            'rooms' => $rooms,
        ]);
    }
}
