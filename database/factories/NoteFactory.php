<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class NoteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'client_id'  => Client::factory(),
            'contact_id' => null,
            'user_id'    => User::factory(),
            'body'       => $this->faker->paragraph(),
        ];
    }
}
