<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\MFeature;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MFeatureController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = $request->input('q');
        $perPage = $request->input('per_page', 10);

        $featuresQuery = MFeature::query();

        if ($query) {
            $featuresQuery->where('name', 'like', '%' . $query . '%')
                         ->orWhere('description', 'like', '%' . $query . '%');
        }

        $features = $featuresQuery->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'total' => $features->total(),
            'page' => $features->currentPage(),
            'per_page' => $features->perPage(),
            'last_page' => $features->lastPage(),
            'data' => $features->items(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'img_url' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 422);
        }

        try {
            $feature = MFeature::create($validator->validated());

            return response()->json([
                'status' => 'success',
                'message' => 'Feature created successfully',
                'data' => $feature
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create feature',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $feature = MFeature::findOrFail($id);

            return response()->json([
                'status' => 'success',
                'data' => $feature
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Feature not found'
            ], 404);
        }
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'img_url' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 422);
        }

        try {
            $feature = MFeature::findOrFail($id);
            $feature->update($validator->validated());

            return response()->json([
                'status' => 'success',
                'message' => 'Feature updated successfully',
                'data' => $feature
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Feature not found'
            ], 404);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $feature = MFeature::findOrFail($id);
            $feature->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Feature deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Feature not found'
            ], 404);
        }
    }
}
