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

        // Créer des cours pour chaque niveau
        Course::factory(7)  // Secondaire inférieur
            ->create()
            ->each(function (Course $course) use ($groups): void {
                $course->groups()->attach(
                    $groups->random(random_int(1, min(3, $groups->count())))->all()
                );
            });

        Course::factory(8)  // Secondaire supérieur
            ->create()
            ->each(function (Course $course) use ($groups): void {
                $course->groups()->attach(
                    $groups->random(random_int(1, min(3, $groups->count())))->all()
                );
            });

        Course::factory(5)  // Supérieur
            ->create()
            ->each(function (Course $course) use ($groups): void {
                $course->groups()->attach(
                    $groups->random(random_int(1, min(3, $groups->count())))->all()
                );
            });
    }
}
