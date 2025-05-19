<?php

namespace Database\Seeders;

use App\Models\MCarCategory;
use Illuminate\Database\Seeder;

class MCarCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'VIP'],
            ['name' => 'REGULER'],
        ];

        foreach ($categories as $category) {
            MCarCategory::create($category);
        }
    }
}
