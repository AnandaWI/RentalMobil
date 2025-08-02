<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\MCarType;
use App\Models\MCarCategory;
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
                'not_available_at' => 'required|date|after:available_at',
                'kategori' => 'nullable|string',
                'destination_id' => 'required|exists:m_destinations,id'
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
            $kategori = $request->kategori;
            $destinationId = $request->destination_id;

            // Ambil category_id jika kategori dikirim
            $categoryId = null;
            if ($kategori) {
                $category = MCarCategory::where('name', $kategori)->first();
                if ($category) {
                    $categoryId = $category->id;
                }
            }

            // Ambil CarType yang memiliki OwnerCar tersedia
            $carTypesQuery = MCarType::whereHas('ownerCars', function ($q) use ($startDate, $endDate) {
                $q->whereDoesntHave('availabilities', function ($query) use ($startDate, $endDate) {
                    $query->where(function ($sub) use ($startDate, $endDate) {
                        $sub->where('not_available_at', '<=', $endDate)
                            ->where('available_at', '>=', $startDate);
                    });
                });
            });

            // Jika kategori ada, tambahkan filter where category_id
            if ($categoryId) {
                $carTypesQuery->where('category_id', $categoryId);
            }

            $carTypes = $carTypesQuery
                ->with(['ownerCars' => function ($q) use ($startDate, $endDate) {
                    $q->whereDoesntHave('availabilities', function ($query) use ($startDate, $endDate) {
                        $query->where(function ($sub) use ($startDate, $endDate) {
                            $sub->where('not_available_at', '<=', $endDate)
                                ->where('available_at', '>=', $startDate);
                        });
                    });
                }])
                ->with('category')
                ->with(['images' => function ($q) {
                    $q->orderBy('id')->limit(1);
                }])
                ->with(['carDestinationPrices' => function ($q) use ($destinationId) {
                    $q->where('destination_id', $destinationId);
                }])
                ->get();

            $result = $carTypes->map(function ($carType) use ($destinationId) {
                // Ambil harga dari carDestinationPrices relasi yang sudah difilter
                $price = $carType->carDestinationPrices->first()->price ?? null;

                return [
                    'id' => $carType->id,
                    'name' => $carType->car_name,
                    'capacity' => $carType->capacity,
                    'rent_price' => $carType->rent_price,
                    'destination_price' => $price, // ➡️ tambahkan di response
                    'count' => $carType->ownerCars->count(),
                    'category' => [
                        'id' => $carType->category->id ?? null,
                        'name' => $carType->category->name ?? null,
                    ],
                    'image' => $carType->images->first() ? [
                        'id' => $carType->images->first()->id,
                        'path' => $carType->images->first()->img_url,
                    ] : null,
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
