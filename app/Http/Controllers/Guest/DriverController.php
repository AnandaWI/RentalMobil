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
            $birthDate = Carbon::parse($driver->tgl_lahir);
            // hitung umur dalam tahun, pastikan positif dan bulat ke bawah
            $driver->tahun = Carbon::now()->diffInYears($birthDate);
            return $driver;
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Driver created successfully',
            'data' => $driversWithAge
        ], 201);
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
