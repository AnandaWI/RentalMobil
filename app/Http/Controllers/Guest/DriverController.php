<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\MDriver;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DriverController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $drivers = MDriver::all();

        $driversWithAge = $drivers->map(function ($driver) {
            if ($driver->tgl_lahir) {
                $birthYear = Carbon::parse($driver->tgl_lahir)->year;
                $currentYear = Carbon::now()->year;
                $driver->tahun = $currentYear - $birthYear;
            } else {
                $driver->tahun = null; // jika kosong
            }

            return $driver;
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Drivers fetched successfully',
            'data' => $driversWithAge
        ], 200);
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $driver = MDriver::find($id);
        return response()->json([
            'status' => 'success',
            'message' => 'Driver created successfully',
            'data' => $driver
        ], 201);
    }
}
