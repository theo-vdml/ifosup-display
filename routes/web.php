<?php

use App\Http\Controllers\CourseController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\RecurrenceController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\ScreenController;
use App\Http\Controllers\ScreenSlideController;
use App\Http\Controllers\TeacherController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::get('screen', [ScreenController::class, 'index'])->name('screen');
Route::get('screen/data', [ScreenController::class, 'data'])->name('screen.data');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
    Route::get('recurrences', [RecurrenceController::class, 'index'])->name('recurrences');
    Route::post('recurrences', [RecurrenceController::class, 'store'])->name('recurrences.store');
    Route::patch('recurrences/{recurrence}', [RecurrenceController::class, 'update'])->name('recurrences.update');
    Route::delete('recurrences/{recurrence}', [RecurrenceController::class, 'destroy'])->name('recurrences.destroy');
    Route::resource('teachers', TeacherController::class);
    Route::resource('rooms', RoomController::class);
    Route::resource('groups', GroupController::class);
    Route::resource('courses', CourseController::class);

    Route::get('scheduler', [ScheduleController::class, 'index'])->name('schedule');
    Route::post('scheduler/assignments', [ScheduleController::class, 'store'])
        ->name('schedule.assignments.store');
    Route::patch('scheduler/assignments/{assignment}', [ScheduleController::class, 'update'])
        ->name('schedule.assignments.update');
    Route::patch('scheduler/assignments/{assignment}/status', [ScheduleController::class, 'updateStatus'])
        ->name('schedule.assignments.update-status');
    Route::delete('scheduler/assignments/{assignment}', [ScheduleController::class, 'destroy'])
        ->name('schedule.assignments.destroy');

    Route::get('screen/slides', [ScreenSlideController::class, 'index'])->name('screen.slides.index');
    Route::post('screen/slides', [ScreenSlideController::class, 'store'])->name('screen.slides.store');
    Route::patch('screen/slides/order', [ScreenSlideController::class, 'reorder'])->name('screen.slides.reorder');
    Route::patch('screen/slides/{screenSlide}', [ScreenSlideController::class, 'update'])->name('screen.slides.update');
    Route::delete('screen/slides/{screenSlide}', [ScreenSlideController::class, 'destroy'])->name('screen.slides.destroy');
});


require __DIR__ . '/settings.php';
