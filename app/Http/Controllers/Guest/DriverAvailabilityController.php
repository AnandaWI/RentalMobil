<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\MDriver;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DriverAvailabilityController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'available_at' => 'required|date',
                'not_available_at' => 'required|date|after:available_at',
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

            $driversQuery = MDriver::whereDoesntHave('availabilities', function ($query) use ($startDate, $endDate) {
                $query->where(function ($sub) use ($startDate, $endDate) {
                    $sub->where('not_available_at', '<=', $endDate)
                        ->where('available_at', '>=', $startDate);
                });
            })->get();

            $result = $driversQuery->map(function ($driver) {
                return [
                    'id' => $driver->id,
                    'name' => $driver->name,
                    'experience' => $driver->pengalaman,
                    'tgl_lahir' => $driver->tgl_lahir,
                    'umur' => Carbon::parse($driver->tgl_lahir)->age,
                    'img_url' => $driver->img_url,
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
