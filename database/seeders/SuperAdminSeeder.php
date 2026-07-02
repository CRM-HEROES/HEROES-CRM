<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class SuperAdminSeeder extends Seeder
{
    public function run()
    {
        Role::create(['name' => 'Super Admin']);
    }
}
