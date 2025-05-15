<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Master\MBankStoreUpdateRequest;
use App\Models\MBank;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class MBankController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $banks = MBank::all();
        return $this->sendSuccess($banks);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MBankStoreUpdateRequest $request): JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('banks', 'public');
        }

        $bank = MBank::create($data);
        return $this->sendSuccess($bank, 'Bank created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $bank = MBank::findOrFail($id);
        return $this->sendSuccess($bank);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(MBankStoreUpdateRequest $request, string $id): JsonResponse
    {
        $bank = MBank::findOrFail($id);
        $data = $request->validated();

        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($bank->logo) {
                Storage::disk('public')->delete($bank->logo);
            }
            $data['logo'] = $request->file('logo')->store('banks', 'public');
        }

        $bank->update($data);
        return $this->sendSuccess($bank, 'Bank updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $bank = MBank::findOrFail($id);

        // Delete logo if exists
        if ($bank->logo) {
            Storage::disk('public')->delete($bank->logo);
        }

        $bank->delete();
        return $this->sendSuccess(null, 'Bank deleted successfully');
    }
}
