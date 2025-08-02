<?php

namespace App\Http\Controllers;

use App\Models\DriverAvailability;
use App\Models\Order;
use App\Models\OwnerCarAvailability;
use Carbon\Carbon;
use Faker\Provider\Base;
use Illuminate\Http\Request;

class OrderController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = $request->input('q');

            $ordersQuery = Order::with(['customer', 'destination', 'orderDetails.car.carType'])
                ->select(['id', 'customer_id', 'destination_id', 'day', 'rent_date', 'pick_up_time', 'total_price', 'detail_destination', 'status', 'created_at']);

            if ($query) {
                $ordersQuery->whereHas('customer', function ($q) use ($query) {
                    $q->where('name', 'like', '%' . $query . '%')
                        ->orWhere('email', 'like', '%' . $query . '%')
                        ->orWhere('phone_number', 'like', '%' . $query . '%');
                });
            }

            $orders = $ordersQuery->paginate(10);

            // Transform data untuk frontend
            $transformedData = $orders->getCollection()->map(function ($order) {
                $carTypes = $order->orderDetails->map(function ($detail) {
                    return $detail->car->carType->car_name ?? 'N/A';
                })->unique()->implode(', ');

                return [
                    'id' => $order->id,
                    'customer_name' => $order->customer->name ?? 'N/A',
                    'email' => $order->customer->email ?? 'N/A',
                    'phone' => $order->customer->phone_number ?? 'N/A',
                    'address' => $order->customer->address ?? 'N/A',
                    'destination' => $order->destination->name ?? 'N/A',
                    'rent_date' => $order->rent_date,
                    'pickup_time' => $order->pick_up_time,
                    'days' => $order->day,
                    'total_price' => 'Rp ' . number_format($order->total_price, 0, ',', '.'),
                    'status' => $order->status,
                    'detail_destination' => $order->detail_destination,
                    'order_date' => $order->created_at->format('d/m/Y H:i'),
                    'car_types' => $carTypes, // ✅ mobil yang disewa
                ];
            });


            return response()->json([
                'total' => $orders->total(),
                'page' => $orders->currentPage(),
                'per_page' => $orders->perPage(),
                'last_page' => $orders->lastPage(),
                'data' => $transformedData,
            ]);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $order = Order::with(['customer', 'destination', 'orderDetails.car.carType', 'orderDetails.driver'])
                ->findOrFail($id);

            $orderDetails = $order->orderDetails->map(function ($detail) {
                return [
                    'car_type' => $detail->car->carType->car_name ?? 'N/A',
                    'plate_number' => $detail->car->plate_number ?? 'N/A',
                    'driver_name' => $detail->driver->name ?? 'Tanpa Driver',
                    'price' => 'Rp ' . number_format($detail->price, 0, ',', '.'),
                ];
            });

            $data = [
                'id' => $order->id,
                'customer' => [
                    'name' => $order->customer->name,
                    'email' => $order->customer->email,
                    'phone' => $order->customer->phone_number,
                    'address' => $order->customer->address,
                ],
                'destination' => $order->destination->name ?? 'N/A',
                'rent_date' => $order->rent_date,
                'pickup_time' => $order->pick_up_time,
                'days' => $order->day,
                'total_price' => 'Rp ' . number_format($order->total_price, 0, ',', '.'),
                'status' => $order->status,
                'detail_destination' => $order->detail_destination,
                'order_date' => $order->created_at->format('d/m/Y H:i'),
                'order_details' => $orderDetails,
            ];

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $order = Order::with('orderDetails')->findOrFail($id);

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
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
    }
}
