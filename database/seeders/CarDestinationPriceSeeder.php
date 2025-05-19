<?php

namespace Database\Seeders;

use App\Models\MCarType;
use App\Models\MDestination;
use App\Models\CarDestinationPrice;
use Illuminate\Database\Seeder;

class CarDestinationPriceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all destinations and car types
        $destinations = MDestination::all();
        $carTypes = MCarType::all();

        foreach ($destinations as $destination) {
            foreach ($carTypes as $carType) {
                // Base price calculation based on car rent price and destination days
                $basePrice = $carType->rent_price * $destination->posibility_day;

                // Add random variation (±10%) to make prices more realistic
                $variation = rand(-10, 10) / 100;
                $finalPrice = $basePrice * (1 + $variation);

                CarDestinationPrice::create([
                    'destination_id' => $destination->id,
                    'car_type_id' => $carType->id,
                    'price' => round($finalPrice, 2)
                ]);
            }
        }
    }
}
