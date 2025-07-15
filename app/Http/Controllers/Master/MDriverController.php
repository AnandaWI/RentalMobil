<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\MDriverStoreUpdateRequest;
use App\Models\MDriver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MDriverController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = $request->input('q');
            $perPage = $request->input('per_page', 10);
            $minExperience = $request->input('min_experience');
            $maxExperience = $request->input('max_experience');

            $driversQuery = MDriver::query();

            if ($query) {
                $driversQuery->where('name', 'like', '%' . $query . '%');
            }

            if ($minExperience !== null) {
                $driversQuery->where('pengalaman', '>=', $minExperience);
            }

            if ($maxExperience !== null) {
                $driversQuery->where('pengalaman', '<=', $maxExperience);
            }

            $drivers = $driversQuery->paginate($perPage);

            return response()->json([
                'total' => $drivers->total(),
                'page' => $drivers->currentPage(),
                'per_page' => $drivers->perPage(),
                'last_page' => $drivers->lastPage(),
                'data' => $drivers->items(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch drivers',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(MDriverStoreUpdateRequest $request): JsonResponse
    {
        try {
            $driver = MDriver::create($request->validated());

            return response()->json([
                'status' => 'success',
                'message' => 'Driver created successfully',
                'data' => $driver
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create driver',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $driver = MDriver::findOrFail($id);

            return response()->json([
                'status' => 'success',
                'data' => $driver
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Driver not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    public function update(MDriverStoreUpdateRequest $request, string $id): JsonResponse
    {
        try {
            $driver = MDriver::findOrFail($id);
            $driver->update($request->validated());

            return response()->json([
                'status' => 'success',
                'message' => 'Driver updated successfully',
                'data' => $driver
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update driver',
                'error' => $e->getMessage()
            ], $e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException ? 404 : 500);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $driver = MDriver::findOrFail($id);
            $driver->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Driver deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete driver',
                'error' => $e->getMessage()
            ], $e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException ? 404 : 500);
        }
    }
}
