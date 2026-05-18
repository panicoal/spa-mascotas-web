<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
// use Spatie\Permission\Models\Role;
use App\Models\Role;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::firstOrCreate([
            'name' => 'ADMIN',
            'guard_name' => 'web'
        ]);

        Role::firstOrCreate([
            'name' => 'RECEPCION',
            'guard_name' => 'web'
        ]);

        Role::firstOrCreate([
            'name' => 'GROOMER',
            'guard_name' => 'web'
        ]);

        Role::firstOrCreate([
            'name' => 'CLIENTE',
            'guard_name' => 'web'
        ]);
    }
}
