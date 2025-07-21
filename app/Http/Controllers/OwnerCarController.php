<?php

namespace App\Http\Controllers;

use App\Http\Requests\OwnerCarStoreUpdateRequest;
use App\Models\Owner;
use App\Models\OwnerCar;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OwnerCarController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = $request->input('q');
        $perPage = $request->input('per_page', 10);

        $carsQuery = OwnerCar::with(['carType', 'owner']);

        if ($query) {
            $carsQuery->where('plate_number', 'like', '%' . $query . '%')
                ->orWhereHas('carType', function ($q) use ($query) {
                    $q->where('car_name', 'like', '%' . $query . '%');
                })
                ->orWhereHas('owner', function ($q) use ($query) {
                    $q->where('name', 'like', '%' . $query . '%');
                });
        }

        $cars = $carsQuery->paginate($perPage);

        return response()->json([
            'total' => $cars->total(),
            'page' => $cars->currentPage(),
            'per_page' => $cars->perPage(),
            'last_page' => $cars->lastPage(),
            'data' => $cars->items(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(OwnerCarStoreUpdateRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['owner_id'] = Owner::first()->id;
        $car = OwnerCar::create($data);
        $car->load(['carType', 'owner']);
        return response()->json([
            'status' => 'success',
            'message' => 'Owner car created successfully',
            'data' => $car
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $car = OwnerCar::with(['carType', 'owner'])->findOrFail($id);
        return response()->json([
            'status' => 'success',
            'data' => $car
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(OwnerCarStoreUpdateRequest $request, string $id): JsonResponse
    {
        $car = OwnerCar::findOrFail($id);
        $car->update($request->validated());
        $car->load(['carType', 'owner']);
        return response()->json([
            'status' => 'success',
            'message' => 'Owner car updated successfully',
            'data' => $car
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $car = OwnerCar::findOrFail($id);
        $car->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Owner car deleted successfully'
        ]);
    }
}
