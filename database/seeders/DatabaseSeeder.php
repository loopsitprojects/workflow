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
        // Default Admin
        User::updateOrCreate(
            ['email' => 'admin@loops.com'],
            [
                'name' => 'Alex Sterling',
                'username' => 'admin',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'role' => 'Admin',
            ]
        );

        $roles = ['Admin', 'Writer', 'Approver', 'Brand Manager', 'Designer', 'Coordinator', 'Traffic Coordinator'];

        foreach ($roles as $role) {
            for ($i = 1; $i <= 2; $i++) {
                $roleSlug = strtolower(str_replace(' ', '', $role));

                User::updateOrCreate(
                    ['email' => "{$roleSlug}{$i}@loops.com"],
                    [
                        'name' => "{$role} User {$i}",
                        'username' => "{$roleSlug}{$i}",
                        'password' => \Illuminate\Support\Facades\Hash::make('password'),
                        'role' => $role,
                    ]
                );
            }
        }
    }
}
