<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Master\MCarCategoryStoreUpdateRequest;
use App\Models\MCarCategory;
use Illuminate\Http\JsonResponse;

class MCarCategoryController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $categories = MCarCategory::all();
        return $this->sendSuccess($categories);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MCarCategoryStoreUpdateRequest $request): JsonResponse
    {
        $category = MCarCategory::create($request->validated());
        return $this->sendSuccess($category, 'Car category created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $category = MCarCategory::findOrFail($id);
        return $this->sendSuccess($category);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(MCarCategoryStoreUpdateRequest $request, string $id): JsonResponse
    {
        $category = MCarCategory::findOrFail($id);
        $category->update($request->validated());
        return $this->sendSuccess($category, 'Car category updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $category = MCarCategory::findOrFail($id);
        $category->delete();
        return $this->sendSuccess(null, 'Car category deleted successfully');
    }
}
