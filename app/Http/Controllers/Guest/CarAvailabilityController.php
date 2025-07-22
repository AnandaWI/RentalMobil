<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\MCarType;
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

            // Ambil CarType yang memiliki OwnerCar tersedia
            $carTypes = MCarType::whereHas('ownerCars', function ($q) use ($startDate, $endDate) {
                $q->whereDoesntHave('availabilities', function ($query) use ($startDate, $endDate) {
                    $query->where(function ($sub) use ($startDate, $endDate) {
                        $sub->where('not_available_at', '<=', $endDate)
                            ->where('available_at', '>=', $startDate);
                    });
                });
            })
                ->with(['ownerCars' => function ($q) use ($startDate, $endDate) {
                    $q->whereDoesntHave('availabilities', function ($query) use ($startDate, $endDate) {
                        $query->where(function ($sub) use ($startDate, $endDate) {
                            $sub->where('not_available_at', '<=', $endDate)
                                ->where('available_at', '>=', $startDate);
                        });
                    });
                }])
                ->with('category')
                ->with((['images' => function ($q) {
                    $q->limit(1);
                }]))
                ->get();

            $result = $carTypes->map(function ($carType) {
                return [
                    'id' => $carType->id,
                    'name' => $carType->car_name,
                    'capacity' => $carType->capacity,
                    'rent_price' => $carType->rent_price,
                    'count' => $carType->ownerCars->count(),
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => $result
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
