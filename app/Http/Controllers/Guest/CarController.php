<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\MCarType;
use App\Models\MService;
use Illuminate\Http\Request;

class CarController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = MCarType::with(['images' => function ($query) {
            $query->orderBy('id')->limit(1);
        }]);

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $cars = $query->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Cars fetched successfully',
            'data' => $cars
        ]);
    }



    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $car = MCarType::with(['images', 'features', 'category'])->find($id);
        return response()->json([
            'status' => 'success',
            'message' => 'car created successfully',
            'data' => $car
        ], 201);
    }
}
