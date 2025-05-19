<?php

namespace Database\Seeders;

use App\Models\MCarType;
use App\Models\Owner;
use App\Models\OwnerCar;
use Illuminate\Database\Seeder;

class OwnerCarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all car types and owners
        $carTypes = MCarType::all();
        $owners = Owner::all();

        // Sample plate numbers format: AB 1234 CD
        $cities = ['B', 'D', 'AB', 'AD', 'AE', 'DR', 'L', 'N'];

        // Each owner will have 2-3 cars
        foreach ($owners as $owner) {
            // Random number of cars (2-3) for each owner
            $numberOfCars = rand(2, 3);

            for ($i = 0; $i < $numberOfCars; $i++) {
                // Generate random plate number
                $city = $cities[array_rand($cities)];
                $number = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
                $letters = chr(rand(65, 90)) . chr(rand(65, 90)); // Random 2 uppercase letters
                $plateNumber = $city . ' ' . $number . ' ' . $letters;

                // Create owner car
                OwnerCar::create([
                    'car_type_id' => $carTypes->random()->id,
                    'owner_id' => $owner->id,
                    'plate_number' => $plateNumber
                ]);
            }
        }
    }
}
