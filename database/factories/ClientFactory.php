<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClientFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name'    => $this->faker->company(),
            'email'   => $this->faker->companyEmail(),
            'phone'   => $this->faker->phoneNumber(),
            'company' => $this->faker->company(),
            'status'  => $this->faker->randomElement(['lead', 'active', 'inactive', 'churned']),
        ];
    }
}
