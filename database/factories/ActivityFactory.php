<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ActivityFactory extends Factory
{
    public function definition(): array
    {
        $completedAt = $this->faker->boolean(40)
            ? $this->faker->dateTimeBetween('-30 days', 'now')
            : null;

        return [
            'client_id'    => Client::factory(),
            'contact_id'   => null,
            'user_id'      => User::factory(),
            'type'         => $this->faker->randomElement(['call', 'email', 'meeting', 'task']),
            'title'        => $this->faker->sentence(4),
            'body'         => $this->faker->optional()->paragraph(),
            'due_at'       => $this->faker->dateTimeBetween('-7 days', '+30 days'),
            'completed_at' => $completedAt,
        ];
    }
}
