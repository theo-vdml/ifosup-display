<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\ScheduleIndexRequest;
use App\Http\Requests\StoreScheduleAssignmentRequest;
use App\Http\Requests\UpdateScheduleAssignmentRequest;
use App\Http\Requests\UpdateScheduleAssignmentStatusRequest;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cookie;
use Inertia\Inertia;
use Inertia\Response;

class ScheduleController extends Controller
{
    public function index(ScheduleIndexRequest $request): Response
    {
        $validated = $request->validated();

        $defaultFromDate = now()->subDay()->toDateString();
        $defaultToDate = now()->addDays(30)->toDateString();

        $fromDate = $this->normalizeDate(
            $validated['from'] ?? $request->cookie('scheduler_from_date'),
            $defaultFromDate,
        );

        $toDate = $this->normalizeDate(
            $validated['to'] ?? $request->cookie('scheduler_to_date'),
            $defaultToDate,
        );

        if ($fromDate > $toDate) {
            [$fromDate, $toDate] = [$toDate, $fromDate];
        }

        Cookie::queue(Cookie::make('scheduler_from_date', $fromDate, 120, '/', null, false, false, false, 'lax'));
        Cookie::queue(Cookie::make('scheduler_to_date', $toDate, 120, '/', null, false, false, false, 'lax'));

        $assignments = Assignment::query()
            ->with(['course', 'room'])
            ->whereDate('date', '>=', $fromDate)
            ->whereDate('date', '<=', $toDate)
            ->get();

        $rooms = Room::all();
        $courses = Course::orderBy('code')->get();

        return Inertia::render('Schedule', [
            'assignments' => $assignments,
            'rooms' => $rooms,
            'courses' => $courses,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
        ]);
    }

    private function normalizeDate(?string $value, string $fallback): string
    {
        if ($value === null || $value === '') {
            return $fallback;
        }

        try {
            return Carbon::createFromFormat('Y-m-d', $value)->toDateString();
        } catch (\Throwable) {
            return $fallback;
        }
    }

    public function store(StoreScheduleAssignmentRequest $request): JsonResponse
    {
        $data = $request->validated();

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

    public function update(UpdateScheduleAssignmentRequest $request, Assignment $assignment): JsonResponse
    {
        $data = $request->validated();

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

    public function updateStatus(
        UpdateScheduleAssignmentStatusRequest $request,
        Assignment $assignment,
    ): JsonResponse {
        $assignment->update($request->validated());

        return response()->json([
            'assignment' => $assignment->load(['course', 'room']),
        ]);
    }

    public function destroy(Assignment $assignment): JsonResponse
    {
        $assignment->delete();

        return response()->json([
            'deleted' => true,
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
