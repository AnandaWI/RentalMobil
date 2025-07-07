<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Master\MCarTypeStoreUpdateRequest;
use App\Models\MCarType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MCarTypeController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {

        $query = $request->input('q');

        $carTypesQuery = MCarType::with('category');

        if ($query) {
            $carTypesQuery->where('name', 'like', '%' . $query . '%');
        }

        $categories = $carTypesQuery->paginate(10);

        return response()->json([
            'total' => $categories->total(),
            'page' => $categories->currentPage(),
            'per_page' => $categories->perPage(),
            'last_page' => $categories->lastPage(),
            'data' => $categories->items(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MCarTypeStoreUpdateRequest $request): JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('img_url')) {
            $data['img_url'] = $request->file('img_url')->store('car-types', 'public');
        }

        $carType = MCarType::create($data);
        return $this->sendSuccess($carType, 'Car type created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $carType = MCarType::with('category')->findOrFail($id);
        return $this->sendSuccess($carType);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(MCarTypeStoreUpdateRequest $request, string $id): JsonResponse
    {
        $carType = MCarType::findOrFail($id);
        $data = $request->validated();

        if ($request->hasFile('img_url')) {
            // Delete old image if exists
            if ($carType->img_url) {
                Storage::disk('public')->delete($carType->img_url);
            }
            $data['img_url'] = $request->file('img_url')->store('car-types', 'public');
        }

        $carType->update($data);
        return $this->sendSuccess($carType, 'Car type updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $carType = MCarType::findOrFail($id);

        // Delete image if exists
        if ($carType->img_url) {
            Storage::disk('public')->delete($carType->img_url);
        }

        $carType->delete();
        return $this->sendSuccess(null, 'Car type deleted successfully');
    }
}
