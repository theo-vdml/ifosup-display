<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Group;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $groups = Group::query()->pluck('id');

        Course::factory(20)
            ->create()
            ->each(function (Course $course) use ($groups): void {
                $course->groups()->attach(
                    $groups->random(random_int(1, min(3, $groups->count())))->all()
                );
            });
    }
}
