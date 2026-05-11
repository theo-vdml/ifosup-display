<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Assignment>
 */
class AssignmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'room_id' => fake()->boolean(90) ? Room::factory() : null,
            'date' => fake()->dateTimeThisMonth()->format('Y-m-d'),
            'period' => fake()->randomElement(['morning', 'afternoon', 'evening']),
            'status' => 'planned',
        ];
    }
}
