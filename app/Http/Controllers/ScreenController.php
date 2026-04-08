<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use Carbon\Carbon;
use Inertia\Inertia;

class ScreenController extends Controller
{

    public function index()
    {
        return Inertia::render('Screen');
    }

    public function data()
    {
        $now = now();

        // On définit les périodes
        $periods = [
            'morning'   => ['00:00:00', '11:59:59'],
            'afternoon' => ['12:00:00', '16:59:59'],
            'evening'   => ['17:00:00', '23:59:59'],
        ];

        // On cherche la CLÉ de la période actuelle
        $currentPeriodKey = collect($periods)->search(function ($times) use ($now) {
            return $now->between(
                Carbon::createFromTimeString($times[0]),
                Carbon::createFromTimeString($times[1])
            );
        });

        // Sécurité : si on ne trouve rien (ex: bug de seconde), on définit une valeur par défaut
        if (!$currentPeriodKey) {
            return collect();
        }

        $periodsOfInterest = [
            'morning'   => ['morning', 'afternoon'],
            'afternoon' => ['afternoon', 'evening'],
            'evening'   => ['evening'],
        ];

        return Assignment::query()
            ->where('date', $now->toDateString())
            ->whereIn('period', $periodsOfInterest[$currentPeriodKey]) // On utilise la clé ici
            ->with(['course', 'room'])
            ->get();
    }
}
