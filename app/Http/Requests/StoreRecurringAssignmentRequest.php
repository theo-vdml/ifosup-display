<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRecurringAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'course_id' => 'required|exists:courses,id',
            'room_id' => 'required|exists:rooms,id',
            'day_of_week' => 'required|integer|between:1,7',
            'period' => 'required|in:morning,afternoon,evening',
            'start_week' => ['required', 'regex:/^\d{4}-W(0[1-9]|[1-4]\d|5[0-3])$/'],
            'end_week' => [
                'required',
                'regex:/^\d{4}-W(0[1-9]|[1-4]\d|5[0-3])$/',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $startWeek = (string) $this->input('start_week');

                    if ((string) $value < $startWeek) {
                        $fail('La semaine de fin doit etre superieure ou egale a la semaine de debut.');
                    }
                },
            ],
        ];
    }
}
