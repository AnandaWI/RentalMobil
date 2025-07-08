<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\MServiceStoreUpdateRequest;
use App\Models\MService;
use App\Models\ServiceImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        try {
            DB::beginTransaction();
            $service = MService::create($request->validated());

            foreach ($request->img_url as $image) {
                ServiceImage::create([
                    'service_id' => $service->id,
                    'img_url' => $image,
                ]);
            }
            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Service created successfully',
                'data' => $service
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'failed',
                'message' => 'Service created failed',
                'error' => $e->getMessage()
            ]);
        }
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
        $service->images()->delete();
        $service->images()->createMany($request->img_url);

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
