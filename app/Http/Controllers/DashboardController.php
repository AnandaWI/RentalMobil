<?php

namespace App\Http\Controllers;

use App\Models\MDestination;
use App\Models\MDriver;
use App\Models\Order;
use App\Models\OwnerCar;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $dashboard = [
                'total_car' => OwnerCar::count(),
                'total_driver' => MDriver::count(),
                'total_transaction' => Order::count(),
                'total_destination' => MDestination::count(),
                'latest_transactions' => Order::with(['customer', 'destination', 'orderDetails.driver', 'orderDetails.car.carType'])
                    ->orderBy('created_at', 'desc')
                    ->take(5)
                    ->get()
                    ->map(function ($order) {
                        return [
                            'id' => $order->id,
                            'customer_name' => $order->customer->name,
                            'destination' => $order->destination->name,
                            'total_price' => $order->total_price,
                            'status' => $order->status,
                            'rent_date' => $order->rent_date,
                            'created_at' => $order->created_at
                        ];
                    })
            ];

            return response()->json([
                'status' => 'success',
                'data' => $dashboard
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch dashboard data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
