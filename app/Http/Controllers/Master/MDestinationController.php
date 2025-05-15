<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Master\MDestinationStoreUpdateRequest;
use App\Models\MDestination;
use Illuminate\Http\JsonResponse;

class MDestinationController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $destinations = MDestination::with(['carDestinationPrices', 'orderDetails'])->get();
        return $this->sendSuccess($destinations);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MDestinationStoreUpdateRequest $request): JsonResponse
    {
        $destination = MDestination::create($request->validated());
        return $this->sendSuccess($destination, 'Destination created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $destination = MDestination::with(['carDestinationPrices', 'orderDetails'])->findOrFail($id);
        return $this->sendSuccess($destination);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(MDestinationStoreUpdateRequest $request, string $id): JsonResponse
    {
        $destination = MDestination::findOrFail($id);
        $destination->update($request->validated());
        return $this->sendSuccess($destination, 'Destination updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $destination = MDestination::findOrFail($id);
        $destination->delete();
        return $this->sendSuccess(null, 'Destination deleted successfully');
    }
}
