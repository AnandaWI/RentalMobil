<?php

namespace Database\Seeders;

use App\Models\MBank;
use App\Models\Owner;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class OwnerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample owner users
        $ownerUsers = [
            [
                'name' => 'John Owner',
                'email' => 'john@rentcar.com',
                'password' => Hash::make('owner123'),
                'email_verified_at' => now(),
            ],
        ];

        // Get all banks
        $banks = MBank::all();

        foreach ($ownerUsers as $ownerData) {
            // Create user
            $user = User::create($ownerData);
            $user->assignRole('owner');

            // Create owner with random bank
            $bank = $banks->random();
            Owner::create([
                'user_id' => $user->id,
                'balance' => 0,
                'bank_id' => $bank->id,
                'bank_no' => fake()->numerify('##########')
            ]);
        }
    }
}
