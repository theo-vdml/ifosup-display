<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use Carbon\Carbon;
use Inertia\Inertia;

class ScreenController extends Controller
{
    private const WELCOME_SLIDE = [
        'key' => 'welcome',
        'type' => 'welcome',
        'data' => [
            'minimumDuration' => 5000,
            'isReady' => true,
        ],
    ];

    private const DEFAULT_SLIDES = [
        [
            'id' => 'schedule-opening',
            'type' => 'schedule',
        ],
        [
            'id' => 'campus-image',
            'type' => 'image',
            'data' => [
                'duration' => 5000,
                'src' => '/easter.jpg',
            ],
        ],
        [
            'id' => 'schedule-middle',
            'type' => 'schedule',
        ],
        [
            'id' => 'campus-video',
            'type' => 'video',
            'data' => [
                'src' => '/easter.mp4',
            ],
        ],
    ];

    private const PERIOD_TIMES = [
        'morning' => ['00:00:00', '12:30:00'],
        'afternoon' => ['12:30:00', '17:30:00'],
        'evening' => ['17:30:00', '23:59:59'],
    ];

    private const PERIOD_LABELS = [
        'morning' => 'Cours du matin',
        'afternoon' => "Cours de l'après-midi",
        'evening' => 'Cours du soir',
    ];

    private const PERIODS_OF_INTEREST = [
        'morning' => ['morning', 'afternoon'],
        'afternoon' => ['afternoon', 'evening'],
        'evening' => ['evening'],
    ];


    public function index()
    {
        return Inertia::render('Screen');
    }

    public function data()
    {
        $timezone = (string) config('app.screen_timezone', 'Europe/Brussels');
        $now = now($timezone);

        $currentPeriodKey = collect(self::PERIOD_TIMES)->search(function (array $times) use ($now) {
            return $now->between(
                Carbon::createFromTimeString($times[0], $now->getTimezone()),
                Carbon::createFromTimeString($times[1], $now->getTimezone())
            );
        });

        $visiblePeriods = $currentPeriodKey
            ? self::PERIODS_OF_INTEREST[$currentPeriodKey]
            : [];

        $assignmentsByPeriod = Assignment::query()
            ->where('date', $now->toDateString())
            ->when($visiblePeriods !== [], function ($query) use ($visiblePeriods) {
                $query->whereIn('period', $visiblePeriods);
            })
            ->with(['course.groups', 'course.teacher', 'room'])
            ->orderByRaw("case period when 'morning' then 1 when 'afternoon' then 2 when 'evening' then 3 end")
            ->get()
            ->groupBy('period');

        $periods = collect($visiblePeriods)
            ->map(function (string $periodKey) use ($assignmentsByPeriod) {
                return [
                    'key' => $periodKey,
                    'title' => self::PERIOD_LABELS[$periodKey],
                    'rows' => $assignmentsByPeriod->get($periodKey, collect())->values(),
                ];
            })
            ->values();

        $slides = collect(self::DEFAULT_SLIDES)
            ->flatMap(function (array $slide) use ($periods) {
                if ($slide['type'] !== 'schedule') {
                    return [[
                        'key' => $slide['id'],
                        'type' => $slide['type'],
                        'data' => $slide['data'],
                    ]];
                }

                return $periods->map(function (array $period) use ($slide) {
                    return [
                        'key' => $slide['id'] . '-' . $period['key'],
                        'type' => 'schedule',
                        'data' => [
                            'title' => $period['title'],
                            'rows' => $period['rows'],
                        ],
                    ];
                });
            })
            ->prepend(self::WELCOME_SLIDE)
            ->values();

        return response()->json([
            'now' => $now->toDateTimeString(),
            'timezone' => $timezone,
            'slides' => $slides,
        ]);
    }
}
