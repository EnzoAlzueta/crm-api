<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Client;
use App\Models\Contact;
use App\Models\Note;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $demo = User::factory()->create([
            'name'     => 'Demo User',
            'email'    => 'demo@crm.test',
            'password' => bcrypt('password'),
        ]);

        $statuses = ['lead', 'active', 'inactive', 'churned'];

        Client::factory(10)
            ->create(['user_id' => $demo->id])
            ->each(function (Client $client) use ($demo, $statuses): void {
                $client->status = $statuses[array_rand($statuses)];
                $client->save();

                $contacts = Contact::factory(rand(2, 4))
                    ->create(['client_id' => $client->id]);

                Note::factory(rand(1, 3))->create([
                    'client_id'  => $client->id,
                    'user_id'    => $demo->id,
                    'contact_id' => $contacts->random()->id,
                ]);

                Activity::factory(rand(1, 3))->create([
                    'client_id'  => $client->id,
                    'user_id'    => $demo->id,
                    'contact_id' => $contacts->random()->id,
                ]);
            });
    }
}
