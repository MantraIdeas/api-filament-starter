<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Superadmin',
                'email' => 'admin@gmail.com',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Add More users if needed
        ];

        foreach ($users as $user) {
            User::create($user);
        }
        $admin = User::first();
        if ($admin) {
            $admin->assignRole('Superadmin');
        }
    }
}
