<?php

namespace Database\Seeders;

use App\Models\MDestination;
use Illuminate\Database\Seeder;

class MDestinationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $destinations = [
            [
                'name' => 'Bali Tour',
                'posibility_day' => 3
            ],
            [
                'name' => 'Yogyakarta City Tour',
                'posibility_day' => 2
            ],
            [
                'name' => 'Bandung City Tour',
                'posibility_day' => 2
            ],
            [
                'name' => 'Malang City Tour',
                'posibility_day' => 2
            ],
            [
                'name' => 'Bromo Mountain Tour',
                'posibility_day' => 2
            ],
            [
                'name' => 'Jakarta City Tour',
                'posibility_day' => 1
            ],
            [
                'name' => 'Surabaya City Tour',
                'posibility_day' => 1
            ]
        ];

        foreach ($destinations as $destination) {
            MDestination::create($destination);
        }
    }
}
