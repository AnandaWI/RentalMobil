<?php

namespace App\Http\Controllers;

use App\Models\DriverAvailability;
use App\Models\Order;
use App\Models\OwnerCarAvailability;
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

            $ordersQuery = Order::with(['customer', 'destination'])
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

            foreach ($order->orderDetails as $detail) {
                // Hapus ketersediaan mobil
                OwnerCarAvailability::where('car_id', $detail->car_id)
                    ->where('date', $order->rent_date)
                    ->delete();

                // Hapus ketersediaan driver
                DriverAvailability::where('driver_id', $detail->driver_id)
                    ->where('date', $order->rent_date)
                    ->delete();
            }

            // Hapus relasi order details
            $order->orderDetails()->delete();

            // Hapus order utama
            $order->delete();

            return response()->json(['success' => true, 'message' => 'Order berhasil dihapus.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
