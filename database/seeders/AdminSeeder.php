<?php

namespace Database\Seeders;

use App\Models\User;
use App\UserRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin User
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'role' => UserRole::Admin,
                'email_verified_at' => now(),
            ]
        );

        // Create Moderator User
        User::updateOrCreate(
            ['email' => 'moderator@example.com'],
            [
                'name' => 'Moderator User',
                'email' => 'moderator@example.com',
                'password' => Hash::make('password'),
                'role' => UserRole::Moderator,
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Admin and Moderator users created successfully!');
        $this->command->info('Admin - Email: admin@example.com, Password: password');
        $this->command->info('Moderator - Email: moderator@example.com, Password: password');
    }
}
