<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\MService;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $services = MService::with(['images' => function ($query) {
            $query->limit(1);
        }])->get();
        return response()->json([
            'status' => 'success',
            'message' => 'Service created successfully',
            'data' => $services
        ], 201);
    }



    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $service = MService::with(['images'])->find($id);
        return response()->json([
            'status' => 'success',
            'message' => 'Service created successfully',
            'data' => $service
        ], 201);
    }
}
