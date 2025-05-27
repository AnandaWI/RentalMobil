<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentRequest;
use App\Models\CarDestinationPrice;
use App\Models\Customer;
use App\Models\HistoryTransaction;
use App\Models\MCarType;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Owner;
use App\Models\OwnerCar;
use App\Models\OwnerCarAvailability;
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
            $order->day = $data['day'];
            $order->rent_date = $data['rent_date'];
            $order->save();

            $cars = $data['order_details'];

            $totalPrice = 0;
            foreach ($cars as $key => $car) {
                $order_detail = new OrderDetail();
                $order_detail->order_id = $order->id;
                $order_detail->car_id = $car;

                $owner_car = OwnerCar::find($car);
                $car_type = MCarType::find($owner_car->car_type_id);
                $car_destination_price = CarDestinationPrice::where('destination_id', $data['destination_id'])->where('car_type_id', $car_type->id)->first();

                $car_destination_price = $car_destination_price->price;
                $rentCarPrice = $car_type->rent_price;

                $formula = $car_destination_price + ($order->day * $rentCarPrice);
                $order_detail->amount = $formula;
                $order_detail->save();
                $totalPrice += $formula;

                $owner_car_availability = new OwnerCarAvailability();
                $owner_car_availability->car_id = $owner_car->id;
                $owner_car_availability->not_available_at = $order->rent_date;
                $rentDate = new DateTime($order->rent_date);
                $rentDate->add(new DateInterval('P' . $order->day . 'D'));
                $owner_car_availability->available_at = $rentDate->format('Y-m-d');
                $owner_car_availability->save();
            }
            $order->total_price = $totalPrice;
            $order->save();

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
                $order->payment_status = 'success';
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
                $order->payment_status = 'failed';
                $order->save();

                OwnerCarAvailability::where('car_id', $order->order_details->car_id)->where('not_available_at', $order->rent_date)->delete();
                return 1;
            }
            return 3;
        }

        return 0;
    }
}
