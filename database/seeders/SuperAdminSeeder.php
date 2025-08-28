<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'superadmin@example.com'], // unique identifier
            [
                'name' => 'Super Admin',
                'phone' => '0712345678',
                'password' => Hash::make('SuperSecret123!'), // always hash password
                'role_id' => 1, // replace with your superadmin role ID
            ]
        );

        $this->command->info('Superadmin user seeded successfully!');
    }
}
