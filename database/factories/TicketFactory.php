<?php

namespace Database\Factories;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\Owner;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'priority' => fake()->randomElement(TicketPriority::cases()),
            'status' => fake()->randomElement(TicketStatus::cases()),
            'owner_id' => Owner::factory(),
            'date_start' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
