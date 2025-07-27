<?php

namespace App\Http\Controllers;

use App\Http\Requests\ManageDestinationRequest;
use App\Models\MCarType;
use App\Models\MDestination;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ManageDestinationController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = $request->input('q');

            $eventsQuery = MDestination::select(['id', 'name', 'posibility_day']);

            if ($query) {
                $eventsQuery->where('name', 'like', '%' . $query . '%');
            }

            $events = $eventsQuery->paginate(10);

            return response()->json([
                'total' => $events->total(),
                'page' => $events->currentPage(),
                'per_page' => $events->perPage(),
                'last_page' => $events->lastPage(),
                'data' => $events->items(),
            ]);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(ManageDestinationRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->validated(); // gunakan validated agar sesuai rules
            $carDestinationPrices = $data['car_destination_price'];
            unset($data['car_destination_price']); // hilangkan dari $data agar tidak masuk ke MDestination

            $destination = MDestination::create($data);

            foreach ($carDestinationPrices as $priceData) {
                $destination->carDestinationPrices()->create([
                    'car_type_id' => $priceData['car_type_id'],
                    'price' => $priceData['price'],
                ]);
            }

            DB::commit();
            return $this->sendSuccess($destination);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError($e->getMessage());
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $data = MDestination::with('carDestinationPrices')->findOrFail($id);
            $listCarTypes = MCarType::all();

            return $this->sendSuccess([
                'data' => $data,
                'list_car_types' => $listCarTypes
            ]);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ManageDestinationRequest $request, string $id)
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();
            $carDestinationPrices = $data['car_destination_price'];
            unset($data['car_destination_price']); // buang dari data utama

            $destination = MDestination::findOrFail($id);

            // Update data utama
            $destination->update($data);

            // Sinkronisasi harga: hapus yang lama, insert yang baru
            $destination->carDestinationPrices()->delete();

            foreach ($carDestinationPrices as $priceData) {
                $destination->carDestinationPrices()->create([
                    'car_type_id' => $priceData['car_type_id'],
                    'price' => $priceData['price'],
                ]);
            }

            DB::commit();
            return $this->sendSuccess($destination, 'Destination updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError($e->getMessage());
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $data = MDestination::findOrFail($id);
            $data->delete();
            return $this->sendSuccess(null);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
    }

    public function carTypeList()
    {
        $data = MCarType::all();
        return $this->sendSuccess($data);
    }
}
