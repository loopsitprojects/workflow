<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Only seed admin1
        User::updateOrCreate(
            ['email' => 'admin1@loops.com'],
            [
                'name' => 'admin1',
                'username' => 'admin1',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'role' => 'Admin',
            ]
        );

        $this->call(SubtaskTypeSeeder::class);
    }
}
