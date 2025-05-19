<?php

namespace Database\Seeders;

use App\Models\MBank;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class MBankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create storage directory if it doesn't exist
        $storagePath = storage_path('app/public/banks');
        if (!File::exists($storagePath)) {
            File::makeDirectory($storagePath, 0755, true);
        }

        $banks = [
            [
                'code' => 'BRI',
                'name' => 'Bank Rakyat Indonesia',
                'logo_url' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/6/68/BANK_BRI_logo.svg/2560px-BANK_BRI_logo.svg.png'
            ],
            [
                'code' => 'BCA',
                'name' => 'Bank Central Asia',
                'logo_url' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/5/5c/Bank_Central_Asia.svg/2560px-Bank_Central_Asia.svg.png'
            ],
            [
                'code' => 'MANDIRI',
                'name' => 'Bank Mandiri',
                'logo_url' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/a/ad/Bank_Mandiri_logo_2016.svg/2560px-Bank_Mandiri_logo_2016.svg.png'
            ],
            [
                'code' => 'BNI',
                'name' => 'Bank Negara Indonesia',
                'logo_url' => 'https://upload.wikimedia.org/wikipedia/id/thumb/5/55/BNI_logo.svg/2560px-BNI_logo.svg.png'
            ],
            [
                'code' => 'CIMB',
                'name' => 'CIMB Niaga',
                'logo_url' => 'https://upload.wikimedia.org/wikipedia/commons/3/38/CIMB_Niaga_logo.svg'
            ],
            [
                'code' => 'PERMATA',
                'name' => 'Bank Permata',
                'logo_url' => 'https://upload.wikimedia.org/wikipedia/commons/f/ff/Permata_Bank_%282024%29.svg'
            ],
            [
                'code' => 'BTN',
                'name' => 'Bank Tabungan Negara',
                'logo_url' => 'https://upload.wikimedia.org/wikipedia/commons/c/ca/BTN_2024.svg'
            ]
        ];

        foreach ($banks as $bank) {
            // Download and save the logo
            $response = Http::get($bank['logo_url']);
            $fileName = strtolower($bank['code']) . '.png';
            $filePath = 'banks/' . $fileName;

            // Save the image to storage
            File::put(storage_path('app/public/' . $filePath), $response->body());

            // Create bank record
            MBank::create([
                'code' => $bank['code'],
                'name' => $bank['name'],
                'logo' => $filePath
            ]);
        }
    }
}
