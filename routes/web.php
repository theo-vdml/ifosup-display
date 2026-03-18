<?php

use App\Http\Controllers\CourseController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\TeacherController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
    Route::resource('teachers', TeacherController::class);
    Route::resource('rooms', RoomController::class);
    Route::resource('groups', GroupController::class);
    Route::resource('courses', CourseController::class);

    Route::get('scheduler', [ScheduleController::class, 'index'])->name('schedule');
    Route::post('scheduler/assignments', [ScheduleController::class, 'store'])
        ->name('schedule.assignments.store');
    Route::patch('scheduler/assignments/{assignment}', [ScheduleController::class, 'update'])
        ->name('schedule.assignments.update');
});


require __DIR__ . '/settings.php';
