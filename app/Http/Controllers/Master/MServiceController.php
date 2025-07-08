<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\MServiceStoreUpdateRequest;
use App\Models\MService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MServiceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = $request->input('q');

        $driversQuery = MService::query();

        if ($query) {
            $driversQuery->where('name', 'like', '%' . $query . '%');
        }

        $drivers = $driversQuery->paginate(10);

        return response()->json([
            'total' => $drivers->total(),
            'page' => $drivers->currentPage(),
            'per_page' => $drivers->perPage(),
            'last_page' => $drivers->lastPage(),
            'data' => $drivers->items(),
        ]);
    }

    public function store(MServiceStoreUpdateRequest $request)
    {
        $service = MService::create($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Service created successfully',
            'data' => $service
        ], 201);
    }

    public function show(string $id)
    {
        $service = MService::findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $service
        ]);
    }

    public function update(MServiceStoreUpdateRequest $request, string $id)
    {
        $service = MService::findOrFail($id);
        $service->update($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Service updated successfully',
            'data' => $service
        ]);
    }

    public function destroy(string $id)
    {
        $service = MService::findOrFail($id);
        $service->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Service deleted successfully'
        ]);
    }
}
