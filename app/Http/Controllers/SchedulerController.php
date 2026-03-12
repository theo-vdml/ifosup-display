<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class SchedulerController extends Controller
{
    public function index()
    {
        return Inertia::render('Scheduler');
    }
}
