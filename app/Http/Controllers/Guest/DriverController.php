<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\MDriver;
use Illuminate\Http\Request;

class DriverController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $drivers = MDriver::all();
        return response()->json([
            'status' => 'success',
            'message' => 'Driver created successfully',
            'data' => $drivers
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $driver = MDriver::with(['images'])->find($id);
        return response()->json([
            'status' => 'success',
            'message' => 'Driver created successfully',
            'data' => $driver
        ], 201);
    }
}
