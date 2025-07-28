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
            $order->pick_up_time = $data['pick_up_time'];
            // $order->pick_up_location = $data['pick_up_location'];
            $order->detail_destination = $data['detail_destination'];
            $order->save();

            $usedDriverIds = collect($data['order_details'])->pluck('driver_id')->filter()->toArray();

            foreach ($data['order_details'] as &$detail) {
                if (empty($detail['driver_id'])) {
                    $availableDrivers = $this->getAvailableDrivers($order->rent_date, $order->day, $usedDriverIds);

                    if ($availableDrivers->isEmpty()) {
                        throw new \Exception('Tidak ada driver yang tersedia dari ' . $order->rent_date->format('Y-m-d') . ' selama ' . $order->day . ' hari.');
                    }

                    $selectedDriver = $availableDrivers->first();
                    $detail['driver_id'] = $selectedDriver->id;
                    $usedDriverIds[] = $selectedDriver->id; // supaya tidak double assign
                }
            }

            $order_details = $data['order_details'];

            $totalPrice = 0;
            foreach ($order_details as $key => $order_detail) {
                $newOrderDetail = new OrderDetail();
                $newOrderDetail->order_id = $order->id;
                $newOrderDetail->car_id = $order_detail['owner_car_type_id'];
                $newOrderDetail->driver_id = $order_detail['driver_id'];

                $owner_car = OwnerCar::find($order_detail['owner_car_type_id']);
                $car_type = MCarType::find($owner_car->car_type_id);
                $car_destination_price = CarDestinationPrice::where('destination_id', $data['destination_id'])->where('car_type_id', $car_type->id)->first();

                $car_destination_price = $car_destination_price->price;
                $rentCarPrice = $car_type->rent_price;

                $formula = $car_destination_price + ($order->day * $rentCarPrice);
                $newOrderDetail->amount = $formula;
                $newOrderDetail->save();
                $totalPrice += $formula;

                $owner_car_availability = new OwnerCarAvailability();
                $owner_car_availability->car_id = $owner_car->id;
                $owner_car_availability->not_available_at = $order->rent_date;
                $rentDate = new DateTime($order->rent_date);
                $rentDate->add(new DateInterval('P' . $order->day . 'D'));
                $owner_car_availability->available_at = $rentDate->format('Y-m-d');
                $owner_car_availability->save();

                $driverAvailability = new DriverAvailability();
                $driverAvailability->driver_id = $order_detail['driver_id'];
                $driverAvailability->not_available_at = $order->rent_date;
                $driverAvailability->available_at = Carbon::parse($order->rent_date)->addDays($order->day)->format('Y-m-d');
                $driverAvailability->save();
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
        $serverKey = config('midtrans.server_key');
        $hashedKey = hash('sha512', $request->order_id . $request->status_code . $request->gross_amount . $serverKey);
        if ($hashedKey == $request->signature_key) {
            if ($request->transaction_status == 'capture' || $request->transaction_status == 'settlement') {
                $order = Order::find($request->order_id);
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
                return 2;
            } else if ($request->transaction_status == 'cancel' || $request->transaction_status == 'deny' || $request->transaction_status == 'expire') {
                $order = Order::find($request->order_id);
                $order->status = 'failed';
                $order->save();

                OwnerCarAvailability::where('car_id', $order->order_details->car_id)->where('not_available_at', $order->rent_date)->delete();
                return 1;
            }
            return 3;
        }

        return 0;
    }
}
