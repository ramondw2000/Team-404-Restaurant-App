<?php

namespace Database\Factories;

use App\Models\Reservation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Reservation>
 */
class ReservationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'guest_name'           => fake()->name(),
            'phone'                => fake()->optional()->phoneNumber(),
            'email'                => fake()->optional()->safeEmail(),
            'party_size'           => fake()->numberBetween(1, 8),
            'reservation_datetime' => now()->addHours(2),
            'table_number'         => null,
            'room_number'          => null,
            'status'               => 'scheduled',
            'internal_notes'       => null,
        ];
    }

    public function scheduled(): static
    {
        return $this->state(['status' => 'scheduled']);
    }

    public function arrived(): static
    {
        return $this->state(['status' => 'arrived']);
    }

    public function late(): static
    {
        return $this->state(['status' => 'late']);
    }

    public function dinner(): static
    {
        return $this->state([
            'reservation_datetime' => now()->setHour(19)->setMinute(0)->setSecond(0),
        ]);
    }

    public function forDate(string $date, string $time = '19:00'): static
    {
        return $this->state([
            'reservation_datetime' => \Carbon\Carbon::parse("{$date} {$time}"),
        ]);
    }
}
