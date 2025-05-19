<?php

namespace Database\Seeders;

use App\Models\MCarCategory;
use App\Models\MCarType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class MCarTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create storage directory if it doesn't exist
        $storagePath = storage_path('app/public/car-types');
        if (!File::exists($storagePath)) {
            File::makeDirectory($storagePath, 0755, true);
        }

        // Get categories
        $vipCategory = MCarCategory::where('name', 'VIP')->first();
        $regulerCategory = MCarCategory::where('name', 'REGULER')->first();

        $carTypes = [
            // VIP Cars
            [
                'category_id' => $vipCategory->id,
                'car_name' => 'Toyota Alphard',
                'capacity' => 7,
                'rent_price' => 1500000,
                'img_url' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRgkqBH_X__asaQM6PiZeod9QU46nnQlw6YoH2anXlrJwQXjuzgZt-n7qa6CS-7SATxd2Y&usqp=CAU'
            ],
            [
                'category_id' => $vipCategory->id,
                'car_name' => 'Mercedes Benz S-Class',
                'capacity' => 5,
                'rent_price' => 2000000,
                'img_url' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRgkqBH_X__asaQM6PiZeod9QU46nnQlw6YoH2anXlrJwQXjuzgZt-n7qa6CS-7SATxd2Y&usqp=CAU'
            ],

            // Regular Cars
            [
                'category_id' => $regulerCategory->id,
                'car_name' => 'Toyota Avanza',
                'capacity' => 7,
                'rent_price' => 400000,
                'img_url' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRgkqBH_X__asaQM6PiZeod9QU46nnQlw6YoH2anXlrJwQXjuzgZt-n7qa6CS-7SATxd2Y&usqp=CAU'
            ],
            [
                'category_id' => $regulerCategory->id,
                'car_name' => 'Honda Brio',
                'capacity' => 5,
                'rent_price' => 350000,
                'img_url' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRgkqBH_X__asaQM6PiZeod9QU46nnQlw6YoH2anXlrJwQXjuzgZt-n7qa6CS-7SATxd2Y&usqp=CAU'
            ],
            [
                'category_id' => $regulerCategory->id,
                'car_name' => 'Toyota Innova',
                'capacity' => 8,
                'rent_price' => 500000,
                'img_url' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRgkqBH_X__asaQM6PiZeod9QU46nnQlw6YoH2anXlrJwQXjuzgZt-n7qa6CS-7SATxd2Y&usqp=CAU'
            ]
        ];

        foreach ($carTypes as $carType) {
            // Download and save the image
            $response = Http::get($carType['img_url']);
            $fileName = Str::random(20) . '.png';
            $filePath = 'car-types/' . $fileName;

            // Save the image to storage
            File::put(storage_path('app/public/' . $filePath), $response->body());

            // Update the img_url to the local path
            $carType['img_url'] = $filePath;

            // Create car type record
            MCarType::create($carType);
        }
    }
}
