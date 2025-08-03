<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentRequest;
use App\Models\CarDestinationPrice;
use App\Models\Customer;
use App\Models\DriverAvailability;
use App\Models\HistoryTransaction;
use App\Models\MCarType;
use App\Models\MDriver;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Owner;
use App\Models\OwnerCar;
use App\Models\OwnerCarAvailability;
use Carbon\Carbon;
use DateInterval;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;  // Tambahkan ini
use Midtrans\Config;

class PaymentController extends BaseController
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');
    }

    private function getAvailableDrivers(Carbon $rentDate, int $day, array $excludeDriverIds = [])
    {
        $startDate = $rentDate->format('Y-m-d'); // not_available_at
        $endDate = $rentDate->copy()->addDays($day)->format('Y-m-d'); // available_at

        $drivers = MDriver::whereNotIn('id', $excludeDriverIds)
            ->whereDoesntHave('availabilities', function ($query) use ($startDate, $endDate) {
                $query->where(function ($sub) use ($startDate, $endDate) {
                    $sub->where('not_available_at', '<=', $endDate)
                        ->where('available_at', '>=', $startDate);
                });
            })->get();

        return $drivers;
    }

    public function store(PaymentRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();

            $customer = new Customer();
            $customer->name = $data['name'];
            $customer->address = $data['address'];
            $customer->phone_number = $data['phone_number'];
            $customer->email = $data['email'];
            $customer->save();

            $order = new Order();
            $order->customer_id = $customer->id;
            $order->destination_id = $data['destination_id'];
            $order->day = (int) $data['day'];
            $order->total_price = $data['total_price'];
            $order->rent_date = Carbon::parse($data['rent_date']);;
            $order->pick_up_time = $data['pickup_time'];
            // $order->pick_up_location = $data['pick_up_location'];
            $order->detail_destination = $data['detail_destination'];
            $order->save();

            $usedDriverIds = collect($data['order_details'])->pluck('driver_id')->filter()->toArray();

            foreach ($data['order_details'] as &$detail) {
                if (empty($detail['driver_id'])) {
                    $availableDrivers = $this->getAvailableDrivers($order->rent_date, $order->day, $usedDriverIds);

                    if ($availableDrivers->isEmpty()) {
                        DB::rollBack();
                        throw new \Exception('Tidak ada driver yang tersedia dari ' . $order->rent_date->format('Y-m-d') . ' selama ' . $order->day . ' hari.');
                    }

                    $selectedDriver = $availableDrivers->first();
                    $detail['driver_id'] = $selectedDriver->id;
                    $usedDriverIds[] = $selectedDriver->id; // supaya tidak double assign
                }
            }

            $order_details = $data['order_details'];

            $totalPrice = 0;
            foreach ($order_details as $order_detail) {
                // Cari mobil berdasarkan car_type_id
                $availableCar = OwnerCar::where('car_type_id', $order_detail['owner_car_type_id'])
                    ->whereDoesntHave('availabilities', function ($query) use ($order) {
                        $startDate = $order->rent_date->format('Y-m-d');
                        $endDate = $order->rent_date->copy()->addDays($order->day)->format('Y-m-d');

                        $query->where(function ($q) use ($startDate, $endDate) {
                            $q->where('not_available_at', '<=', $endDate)
                                ->where('available_at', '>=', $startDate);
                        });
                    })
                    ->first();

                if (!$availableCar) {
                    DB::rollBack();
                    throw new \Exception('Tidak ada mobil yang tersedia dengan tipe tersebut dari ' . $order->rent_date->format('Y-m-d') . ' selama ' . $order->day . ' hari.');
                }

                $car_type = MCarType::find($availableCar->car_type_id);
                $car_destination_price = CarDestinationPrice::where('destination_id', $data['destination_id'])
                    ->where('car_type_id', $car_type->id)
                    ->first();

                $car_destination_price_value = $car_destination_price?->price ?? 0;
                $rentCarPrice = $car_type->rent_price;

                $formula = $car_destination_price_value + ($order->day * $rentCarPrice);

                $newOrderDetail = new OrderDetail();
                $newOrderDetail->order_id = $order->id;
                $newOrderDetail->car_id = $availableCar->id; // gunakan id mobil yang ditemukan
                $newOrderDetail->driver_id = $order_detail['driver_id'];
                $newOrderDetail->amount = $formula;
                $newOrderDetail->save();

                // simpan ketersediaan mobil
                $owner_car_availability = new OwnerCarAvailability();
                $owner_car_availability->car_id = $availableCar->id;
                $owner_car_availability->not_available_at = $order->rent_date;
                $rentDate = new DateTime($order->rent_date);
                $rentDate->add(new DateInterval('P' . $order->day . 'D'));
                $owner_car_availability->available_at = $rentDate->format('Y-m-d');
                $owner_car_availability->save();

                // simpan ketersediaan driver
                $driverAvailability = new DriverAvailability();
                $driverAvailability->driver_id = $order_detail['driver_id'];
                $driverAvailability->not_available_at = $order->rent_date;
                $driverAvailability->available_at = Carbon::parse($order->rent_date)->addDays($order->day)->format('Y-m-d');
                $driverAvailability->save();

                $totalPrice += $formula;
            }

            // Masuk ke Midtrans
            $params = array(
                'transaction_details' => array(
                    'order_id' => $order->id,
                    'gross_amount' => $order->total_price,
                )
            );
            $snapToken = \Midtrans\Snap::getSnapToken($params);
            $order->snap_token = $snapToken;
            $order->save();

            DB::commit();
            return $this->sendSuccess([
                'amount' => $order->total_price,
                'snap_token' => $order->snap_token
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError($e->getMessage());
        }
    }

    public function callback(Request $request)
    {
        Log::info('Callback received', $request->all());

        $serverKey = config('midtrans.server_key');
        $hashedKey = hash('sha512', $request->order_id . $request->status_code . $request->gross_amount . $serverKey);

        Log::info("Generated signature: $hashedKey | From Request: " . $request->signature_key);

        if ($hashedKey == $request->signature_key) {
            $order = Order::find($request->order_id);
            if (!$order) {
                Log::error("Order ID {$request->order_id} not found.");
                return 0;
            }

            if ($request->transaction_status == 'capture' || $request->transaction_status == 'settlement') {
                Log::info("Transaction success. Sending email to customer...");
                try {
                    $order->status = 'success';
                    $order->save();

                    $owner = Owner::first();
                    $owner->balance += $order->total_price;
                    $owner->save();

                    $history_transaction = new HistoryTransaction();
                    $history_transaction->owner_id = $owner->id;
                    $history_transaction->transaction_type = 'in';
                    $history_transaction->order_id = $order->id;
                    $history_transaction->amount = $order->total_price;
                    $history_transaction->balance_now = $owner->balance;
                    $history_transaction->save();

                    // ⛔️ Error kemungkinan terjadi di sini:
                    $this->sendPaymentSuccessEmail($order);

                    Log::info("Email sent successfully for order ID {$order->id}");
                } catch (\Exception $e) {
                    Log::error("Error in callback success logic: " . $e->getMessage());
                }

                return 2;
            }
        }

        return 0;
    }


    /**
     * Kirim email notifikasi pembayaran sukses ke customer
     */
    private function sendPaymentSuccessEmail($order)
    {
        try {
            // Load relasi yang diperlukan
            $order->load(['customer', 'destination', 'orderDetails.car.carType.category', 'orderDetails.driver']);

            $customerEmail = $order->customer->email;
            $customerName = $order->customer->name;

            // Buat detail mobil yang disewa
            $carDetails = $order->orderDetails->map(function ($detail) {
                return [
                    'car_type' => $detail->car->carType->car_name ?? 'N/A',
                    'driver_name' => $detail->driver->name ?? 'N/A',
                    'amount' => 'Rp ' . number_format($detail->amount, 0, ',', '.'),
                    'category' => $detail->car->carType->category->name ?? 'N/A'
                ];
            });

            // Cek apakah ada mobil VIP (yang memerlukan driver)
            $hasVipCar = $carDetails->contains('category', 'VIP');

            // Template email HTML
            $emailContent = $this->getEmailTemplate($order, $carDetails, $hasVipCar);

            // Kirim email
            Mail::html($emailContent, function ($message) use ($customerEmail, $customerName, $order) {
                $message->to($customerEmail, $customerName)
                    ->subject('Konfirmasi Pembayaran Rental Mobil - Order #' . $order->id);
            });
        } catch (\Exception $e) {
            // Log error jika email gagal terkirim
            Log::error('Failed to send payment success email: ' . $e->getMessage());
        }
    }

    /**
     * Template email HTML untuk konfirmasi pembayaran
     */
    private function getEmailTemplate($order, $carDetails, $hasVipCar = false)
    {
        $biayaCarDestinasi = 0;
        $orderDetailsHtml = '';
        $totalPrice = $order->total_price;

        foreach ($order->orderDetails as $detail) {
            $carType = $detail->car->carType->car_name;
            $driverName = $detail->driver->name;
            $carAmount = $detail->amount;
            $biayaCarDestinasi += $carAmount;

            $orderDetailsHtml .= '<tr>';
            $orderDetailsHtml .= '<td style="padding: 8px; border-bottom: 1px solid #ddd;">' . $carType . '</td>';
            $orderDetailsHtml .= '<td style="padding: 8px; border-bottom: 1px solid #ddd;">' . $driverName . '</td>';
            $orderDetailsHtml .= '<td style="padding: 8px; border-bottom: 1px solid #ddd; text-align: right;">' . number_format($carAmount, 0, ',', '.') . '</td>';
            $orderDetailsHtml .= '</tr>';
        }

        $biayaLain = $totalPrice - $biayaCarDestinasi;
        $biayaLainHtml = '<tr>
    <td colspan="2" style="padding: 8px; border-bottom: 1px solid #ddd;">Biaya Lain</td>
    <td style="padding: 8px; border-bottom: 1px solid #ddd; text-align: right;">Rp ' . number_format($biayaLain, 0, ',', '.') . '</td>
</tr>';


        $customerName = htmlspecialchars($order->customer->name);
        $customerAddress = htmlspecialchars($order->customer->address);
        $orderId = htmlspecialchars($order->id);
        $destinationName = htmlspecialchars($order->destination->name ?? 'N/A');
        $detailDestination = htmlspecialchars($order->detail_destination);
        Carbon::setLocale('id');
        $rentDate = Carbon::parse($order->rent_date)->translatedFormat('d F Y');
        $day = htmlspecialchars($order->day);
        $pickUpTime = htmlspecialchars($order->pick_up_time);
        $totalPrice = 'Rp ' . number_format($totalPrice, 0, ',', '.');

        // Header tabel berdasarkan apakah ada mobil VIP atau tidak
        $tableHeader = '<th style="padding: 10px; border-bottom: 1px solid #ddd; text-align: left;">Tipe Mobil</th>
            <th style="padding: 10px; border-bottom: 1px solid #ddd; text-align: left;">Driver</th>
            <th style="padding: 10px; border-bottom: 1px solid #ddd; text-align: right;">Harga</th>';

        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Konfirmasi Pembayaran</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <div style="background-color: #DAA520; color: white; padding: 20px; text-align: center;">
            <h1 style="margin: 0;">AEM Rentcar</h1>
            <p style="margin: 5px 0 0 0;">Konfirmasi Pembayaran</p>
        </div>
        
        <div style="padding: 20px; background-color: #f8f9fa;">
            <h2 style="color: #28a745; margin-top: 0;">✅ Pembayaran Berhasil!</h2>
            <p>Halo <strong>' . $customerName . '</strong>,</p>
            <p>Terima kasih! Pembayaran Anda telah berhasil diproses. Berikut adalah detail pesanan Anda:</p>
        </div>
        
        <div style="padding: 20px;">
            <h3>Detail Pesanan</h3>
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                <tr>
                    <td style="padding: 8px; font-weight: bold;">Order ID:</td>
                    <td style="padding: 8px;">#' . $orderId . '</td>
                </tr>
                <tr>
                    <td style="padding: 8px; font-weight: bold;">Destinasi:</td>
                    <td style="padding: 8px;">' . $destinationName . '</td>
                </tr>
                <tr>
                    <td style="padding: 8px; font-weight: bold;">Detail Destinasi:</td>
                    <td style="padding: 8px;">' . $detailDestination . '</td>
                </tr>
                <tr>
                    <td style="padding: 8px; font-weight: bold;">Alamat Penjemputan:</td>
                    <td style="padding: 8px;">' . $customerAddress . '</td>
                </tr>
                <tr>
                    <td style="padding: 8px; font-weight: bold;">Tanggal Sewa:</td>
                    <td style="padding: 8px;">' . $rentDate . '</td>
                </tr>
                <tr>
                    <td style="padding: 8px; font-weight: bold;">Durasi:</td>
                    <td style="padding: 8px;">' . $day . ' hari</td>
                </tr>
                <tr>
                    <td style="padding: 8px; font-weight: bold;">Waktu Jemput:</td>
                    <td style="padding: 8px;">' . $pickUpTime . '</td>
                </tr>
            </table>
            
            <h3>Detail Mobil' . ($hasVipCar ? ' & Driver' : '') . '</h3>
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px; border: 1px solid #ddd;">
                <thead>
                    <tr style="background-color: #f8f9fa;">
                        ' . $tableHeader . '
                    </tr>
                </thead>
                <tbody>
                    ' . $orderDetailsHtml . '
                    ' . $biayaLainHtml . '
                </tbody>
            </table>
            
            <div style="text-align: right; font-size: 18px; font-weight: bold; color: #DAA520;">
                Total Pembayaran: ' . $totalPrice . '
            </div>
        </div>
        
        <div style="background-color: #e9ecef; padding: 20px; text-align: center;">
            <p style="margin: 0; color: #6c757d;">
                Admin akan menghubungi Anda 1 hari sebelum keberangkatan.<br>
                Jika Anda memiliki pertanyaan, silakan hubungi admin kami.<br>
                Terima kasih telah menggunakan layanan AEM Rentcar!
            </p>
        </div>
    </div>
</body>
</html>';
    }

    public function closeModal($snap_token)
    {
        $order = Order::with('orderDetails')->where('snap_token', $snap_token)->first();

        if (!$order) {
            return $this->sendError('Order tidak ditemukan.');
        }

        // Hitung tanggal awal & akhir dalam format Y-m-d
        $notAvailableAt = Carbon::parse($order->rent_date)->format('Y-m-d');
        $availableAt = Carbon::parse($order->rent_date)
            ->copy()
            ->addDays((int) $order->day)
            ->format('Y-m-d');

        foreach ($order->orderDetails as $detail) {
            // Hapus OwnerCarAvailability
            if ($detail->car_id) {
                OwnerCarAvailability::where('car_id', $detail->car_id)
                    ->where('available_at', $availableAt)
                    ->where('not_available_at', $notAvailableAt)
                    ->delete();
            }

            // Hapus DriverAvailability
            if ($detail->driver_id) {
                DriverAvailability::where('driver_id', $detail->driver_id)
                    ->where('available_at', $availableAt)
                    ->where('not_available_at', $notAvailableAt)
                    ->delete();
            }
        }

        // Hapus order
        $order->delete();

        return $this->sendSuccess(null, 'Order dan data ketersediaan berhasil dihapus.');
    }
}
