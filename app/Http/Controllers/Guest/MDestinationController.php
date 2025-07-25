<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\MDestination;
use Illuminate\Http\Request;

class MDestinationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = MDestination::all();
        return response()->json([
            'status' => 'success',
            'data' => $data
        ], 200);
    }
}
