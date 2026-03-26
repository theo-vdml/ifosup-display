<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRecurringAssignmentRequest;
use App\Http\Requests\UpdateRecurringAssignmentRequest;
use App\Models\Course;
use App\Models\RecurringAssignment;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;
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

        RecurringAssignment::query()->create($data);

        return back();
    }

    public function update(UpdateRecurringAssignmentRequest $request, RecurringAssignment $recurrence): RedirectResponse
    {
        $data = $request->validated();

        $recurrence->update($data);

        return back();
    }

    public function destroy(RecurringAssignment $recurrence): RedirectResponse
    {
        $recurrence->delete();

        return back();
    }
}
