<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Membuat role
        $roles = ['admin', 'owner'];
        foreach ($roles as $role) {
            Role::create(['name' => $role, 'guard_name' => 'web']);
        }

        // Membuat user admin
        $user = User::create([
            'name' => 'Admin',
            'email' => 'adminrentcar@gmail.com',
            'password' => bcrypt('admin123'),
            'email_verified_at' => now(),
        ]);
        $user->assignRole('admin');
    }
}
