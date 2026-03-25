<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Room;
use Inertia\Inertia;
use Inertia\Response;

class RecurrenceController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Recurrences', [
            'rooms' => Room::query()->orderBy('name')->get(),
            'courses' => Course::query()->orderBy('code')->get(),
        ]);
    }
}
