<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Attendance>
 */
class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $date = fake()->date();

        return [
            'user_id' => User::factory(),
            'date' => $date,
            'clock_in' => "{$date} 07:55:00",
            'clock_in_lat' => null,
            'clock_in_long' => null,
            'clock_out' => "{$date} 16:35:00",
            'clock_out_lat' => null,
            'clock_out_long' => null,
            'status' => 'HADIR',
        ];
    }
}
