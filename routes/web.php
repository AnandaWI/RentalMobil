<?php

use Illuminate\Support\Facades\Route;
// use Illuminate\Support\Facades\Mail;
// use Illuminate\Support\Facades\Log;

Route::get('/', function () {
    return response()->json([
        "message" => "Welcome to the API"
    ]);
});



// Route::get('/test-email', function () {
//     try {
//         Log::info('Testing email configuration...');
//         Log::info('Mail Mailer: ' . config('mail.default'));
//         Log::info('Mail Host: ' . config('mail.mailers.smtp.host'));
//         Log::info('Mail Port: ' . config('mail.mailers.smtp.port'));
//         Log::info('Mail Username: ' . config('mail.mailers.smtp.username'));
//         Log::info('Mail From Address: ' . config('mail.from.address'));

//         // Test email langsung tanpa queue
//         Mail::send([], [], function ($message) {
//             $message->to('anandaizulhaq678@gmail.com')
//                 ->subject('Test Email - ' . now())
//                 ->html('<h1>Test Email</h1><p>Ini email test dari Laravel pada ' . now() . '</p>');
//         });

//         Log::info('Test email sent successfully');
//         return 'Email test berhasil dikirim ke anandaizulhaq678@gmail.com pada ' . now();
//     } catch (\Exception $e) {
//         Log::error('Test email failed: ' . $e->getMessage());
//         Log::error('Stack trace: ' . $e->getTraceAsString());
//         return 'Gagal kirim email: ' . $e->getMessage();
//     }
// });
