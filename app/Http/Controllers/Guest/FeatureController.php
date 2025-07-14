<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\MFeature;
use Illuminate\Http\Request;

class FeatureController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $features = MFeature::with(['images' => function ($query) {
            $query->limit(1);
        }])->get();
        return response()->json([
            'status' => 'success',
            'message' => 'Feature created successfully',
            'data' => $features
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $feature = MFeature::with(['images'])->find($id);
        return response()->json([
            'status' => 'success',
            'message' => 'Feature created successfully',
            'data' => $feature
        ], 201);
    }
}
