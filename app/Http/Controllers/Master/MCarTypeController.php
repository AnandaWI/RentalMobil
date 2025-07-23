<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Master\MCarTypeStoreUpdateRequest;
use App\Models\MCarType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class MCarTypeController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = $request->input('q');
        $categoryId = $request->input('category_id');

        $carTypesQuery = MCarType::with('category');

        if ($query) {
            $carTypesQuery->where('car_name', 'like', '%' . $query . '%');
        }

        if ($categoryId) {
            $carTypesQuery->where('category_id', $categoryId);
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
        DB::beginTransaction();
        try {
            $data = $request->validated();

            Log::info('Creating new car type with data:', $data);

            // Create main car type record
            $carType = MCarType::create([
                'car_name' => $data['car_name'],
                'category_id' => $data['category_id'],
                'capacity' => $data['capacity'],
                'rent_price' => $data['rent_price'],
                'description' => $data['description'],
            ]);

            // Create features if provided
            if (isset($data['feature']) && is_array($data['feature'])) {
                foreach ($data['feature'] as $featureData) {
                    if (!empty(trim($featureData))) {
                        $carType->features()->create([
                            'car_type_id' => $carType->id,
                            'feature' => trim($featureData)
                        ]);
                    }
                }
            }

            // Create images if provided
            if (isset($data['img_url']) && is_array($data['img_url'])) {
                foreach ($data['img_url'] as $imgUrl) {
                    if (!empty(trim($imgUrl))) {
                        $carType->images()->create([
                            'car_type_id' => $carType->id,
                            'img_url' => trim($imgUrl)
                        ]);
                    }
                }
            }

            DB::commit();

            // Load relationships for response
            $carType->load(['category', 'features', 'images']);

            Log::info('Car type created successfully:', ['id' => $carType->id]);
            return $this->sendSuccess($carType, 'Car type created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating car type:', ['error' => $e->getMessage()]);
            return $this->sendError($e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $carType = MCarType::with(['category', 'features', 'images'])->findOrFail($id);
        return $this->sendSuccess($carType);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(MCarTypeStoreUpdateRequest $request, string $id): JsonResponse
    {
        DB::beginTransaction();
        try {
            $carType = MCarType::findOrFail($id);
            $data = $request->validated();

            Log::info('Updating car type with data:', [
                'id' => $id,
                'data' => $data
            ]);

            // Update main car type fields
            $carType->update([
                'car_name' => $data['car_name'],
                'category_id' => $data['category_id'],
                'capacity' => $data['capacity'],
                'rent_price' => $data['rent_price'],
                'description' => $data['description'],
            ]);

            // Update features - delete old ones and create new ones
            if (isset($data['feature']) && is_array($data['feature'])) {
                Log::info('Updating features:', $data['feature']);

                // Delete existing features
                $carType->features()->delete();

                // Create new features
                foreach ($data['feature'] as $featureData) {
                    if (!empty(trim($featureData))) {
                        $carType->features()->create([
                            'car_type_id' => $carType->id,
                            'feature' => trim($featureData)
                        ]);
                    }
                }
            }

            // Update images - delete old ones and create new ones
            if (isset($data['img_url']) && is_array($data['img_url'])) {
                Log::info('Updating images:', $data['img_url']);

                // Delete existing images
                $carType->images()->delete();

                // Create new images
                foreach ($data['img_url'] as $imgUrl) {
                    if (!empty(trim($imgUrl))) {
                        $carType->images()->create([
                            'car_type_id' => $carType->id,
                            'img_url' => trim($imgUrl)
                        ]);
                    }
                }
            }

            DB::commit();

            // Load fresh data with relationships
            $carType->load(['category', 'features', 'images']);

            Log::info('Car type updated successfully:', ['id' => $carType->id]);
            return $this->sendSuccess($carType, 'Car type updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating car type:', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->sendError($e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        DB::beginTransaction();
        try {
            $carType = MCarType::with(['features', 'images'])->findOrFail($id);

            // Delete related features and images (if using cascade delete, this might be automatic)
            $carType->features()->delete();
            $carType->images()->delete();

            // Delete the main record
            $carType->delete();

            DB::commit();

            Log::info('Car type deleted successfully:', ['id' => $id]);
            return $this->sendSuccess(null, 'Car type deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting car type:', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            return $this->sendError($e->getMessage());
        }
    }
}
