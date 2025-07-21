<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\OwnerCar;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CarAvailabilityController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'available_at' => 'required|date',
                'not_available_at' => 'required|date|after:available_at'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $startDate = $request->available_at;
            $endDate = $request->not_available_at;

            // Ambil semua mobil dengan relasinya
            $query = OwnerCar::with(['carType', 'owner', 'availabilities'])
                ->whereDoesntHave('availabilities', function ($q) use ($startDate, $endDate) {
                    $q->where(function ($query) use ($startDate, $endDate) {
                        // Cek apakah ada overlap dengan rentang tanggal yang diminta
                        $query->where(function ($q) use ($startDate, $endDate) {
                            $q->where('not_available_at', '<=', $endDate)
                              ->where('available_at', '>=', $startDate);
                        });
                    });
                });

            $cars = $query->get()->map(function ($car) {
                return [
                    'id' => $car->id,
                    'plate_number' => $car->plate_number,
                    'car_type' => [
                        'id' => $car->carType->id,
                        'name' => $car->carType->car_name,
                        'capacity' => $car->carType->capacity,
                        'rent_price' => $car->carType->rent_price
                    ],
                    'owner' => [
                        'id' => $car->owner->id,
                        'name' => $car->owner->name
                    ],
                    'availabilities' => $car->availabilities->map(function ($availability) {
                        return [
                            'not_available_at' => $availability->not_available_at,
                            'available_at' => $availability->available_at
                        ];
                    })
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => $cars
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch car availabilities',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
