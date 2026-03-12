<?php

use App\Http\Controllers\CourseController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\SchedulerController;
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

    Route::get('scheduler', [SchedulerController::class, 'index'])->name('scheduler');
});


require __DIR__ . '/settings.php';
