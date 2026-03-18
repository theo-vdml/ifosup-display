<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\Room;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ScheduleController extends Controller
{
    public function index(): Response
    {
        $assignments = Assignment::all();
        $rooms = Room::all();
        $courses = Course::orderBy('code')->get();

        return Inertia::render('Schedule', [
            'assignments' => $assignments,
            'rooms' => $rooms,
            'courses' => $courses,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'course_id' => ['required', 'exists:courses,id'],
            'room_id' => ['required', 'exists:rooms,id'],
            'date' => ['required', 'date_format:Y-m-d'],
            'period' => ['required', 'in:morning,afternoon,evening'],
        ]);

        if ($this->slotIsOccupied((int) $data['room_id'], (string) $data['date'], (string) $data['period'])) {
            return response()->json([
                'message' => 'Ce créneau est déjà occupé.',
            ], 422);
        }

        $assignment = Assignment::create($data)->load(['course', 'room']);

        return response()->json([
            'assignment' => $assignment,
        ], 201);
    }

    public function update(Request $request, Assignment $assignment): JsonResponse
    {
        $data = $request->validate([
            'room_id' => ['required', 'exists:rooms,id'],
            'date' => ['required', 'date_format:Y-m-d'],
            'period' => ['required', 'in:morning,afternoon,evening'],
        ]);

        if ($this->slotIsOccupied(
            (int) $data['room_id'],
            (string) $data['date'],
            (string) $data['period'],
            $assignment->id,
        )) {
            return response()->json([
                'message' => 'Ce créneau est déjà occupé.',
            ], 422);
        }

        $assignment->update($data);

        return response()->json([
            'assignment' => $assignment->load(['course', 'room']),
        ]);
    }

    private function slotIsOccupied(
        int $roomId,
        string $date,
        string $period,
        ?int $ignoreAssignmentId = null,
    ): bool {
        return Assignment::query()
            ->when($ignoreAssignmentId !== null, fn($query) => $query->where('id', '!=', $ignoreAssignmentId))
            ->where('room_id', $roomId)
            ->whereDate('date', $date)
            ->where('period', $period)
            ->exists();
    }
}
