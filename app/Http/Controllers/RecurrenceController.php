<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRecurringAssignmentRequest;
use App\Http\Requests\UpdateRecurringAssignmentRequest;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\RecurringAssignment;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class RecurrenceController extends Controller
{
    public function index(): Response
    {

        $recurringAssignments = RecurringAssignment::query()
            ->with(['course', 'room'])
            ->orderBy('created_at')
            ->get();

        return Inertia::render('Recurrences', [
            'rooms' => Room::query()->orderBy('name')->get(),
            'courses' => Course::query()->orderBy('code')->get(),
            'recurringAssignments' => $recurringAssignments,
        ]);
    }

    public function store(StoreRecurringAssignmentRequest $request): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data): void {
            $recurrence = RecurringAssignment::query()->create($data);
            $this->syncAssignments($recurrence);
        });

        return back();
    }

    public function update(UpdateRecurringAssignmentRequest $request, RecurringAssignment $recurrence): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($recurrence, $data): void {
            $recurrence->update($data);
            $this->syncAssignments($recurrence);
        });

        return back();
    }

    public function destroy(RecurringAssignment $recurrence): RedirectResponse
    {
        $recurrence->delete();

        return back();
    }

    private function syncAssignments(RecurringAssignment $recurrence): void
    {
        Assignment::query()
            ->where('recurring_assignment_id', $recurrence->id)
            ->where('is_detached', false)
            ->delete();

        $rangeStart = $this->isoWeekToMonday($recurrence->start_week);
        $rangeEnd = $this->isoWeekToMonday($recurrence->end_week)->addDays(6);

        $firstOccurrence = $rangeStart->copy()->addDays(
            ($recurrence->day_of_week - $rangeStart->isoWeekday() + 7) % 7,
        );

        $now = now();
        $rows = [];

        for ($cursor = $firstOccurrence; $cursor->lte($rangeEnd); $cursor->addWeek()) {
            $rows[] = [
                'course_id' => $recurrence->course_id,
                'room_id' => $recurrence->room_id,
                'date' => $cursor->toDateString(),
                'period' => $recurrence->period,
                'recurring_assignment_id' => $recurrence->id,
                'is_detached' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if ($rows !== []) {
            Assignment::query()->insert($rows);
        }
    }

    private function isoWeekToMonday(string $isoWeek): Carbon
    {
        preg_match('/^(\d{4})-W(\d{2})$/', $isoWeek, $matches);

        $year = (int) $matches[1];
        $week = (int) $matches[2];

        $jan4 = Carbon::create($year, 1, 4, 0, 0, 0, 'UTC')->startOfDay();
        $weekOneMonday = $jan4->copy()->subDays($jan4->isoWeekday() - 1);

        return $weekOneMonday->addWeeks($week - 1);
    }
}
