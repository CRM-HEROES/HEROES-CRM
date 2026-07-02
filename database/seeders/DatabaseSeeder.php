<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed the Super Admin role
        $this->call(SuperAdminSeeder::class);

        // Seed the first user as Super Admin
        $user = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'password' => bcrypt('password'), // Replace with your desired password
        ]);

        $superAdminRole = Role::findByName('Super Admin');
        $user->assignRole($superAdminRole);
    }
}
