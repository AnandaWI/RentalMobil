<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\MDriverStoreUpdateRequest;
use App\Models\MDriver;
use Illuminate\Http\Request;

class MDriverController extends Controller
{
    public function index(Request $request)
    {
        $drivers = MDriver::all();
        return response()->json([
            'status' => 'success',
            'data' => $drivers
        ]);
    }

    public function store(MDriverStoreUpdateRequest $request)
    {
        $driver = MDriver::create($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Driver created successfully',
            'data' => $driver
        ], 201);
    }

    public function show(string $id)
    {
        $driver = MDriver::findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $driver
        ]);
    }

    public function update(MDriverStoreUpdateRequest $request, string $id)
    {
        $driver = MDriver::findOrFail($id);
        $driver->update($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Driver updated successfully',
            'data' => $driver
        ]);
    }

    public function destroy(string $id)
    {
        $driver = MDriver::findOrFail($id);
        $driver->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Driver deleted successfully'
        ]);
    }
}
