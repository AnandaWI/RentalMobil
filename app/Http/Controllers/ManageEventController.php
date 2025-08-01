<?php

namespace App\Http\Controllers;

use App\Http\Requests\ManageEventControllerRequest;
use App\Models\Event;
use Illuminate\Http\Request;

class ManageEventController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = $request->input('q');
            $isPublished = $request->input('is_published');

            $eventsQuery = Event::select(['id', 'subject', 'publish_date', 'is_published']);

            if ($query) {
                $eventsQuery->where('subject', 'like', '%' . $query . '%');
            }

            if ($isPublished) {
                $eventsQuery->where('is_published', $isPublished);
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
    public function store(ManageEventControllerRequest $request)
    {
        try {
            $data = $request->validated();
            $event = Event::create($data);

            return $this->sendSuccess($event);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        try {
            $event = Event::findOrFail($id);

            return $this->sendSuccess($event);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        try {
            $data = $request->validated();
            $event = Event::findOrFail($id);

            $event->update($data);

            return $this->sendSuccess($event);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        try {
            $event = Event::findOrFail($id);
            $event->delete();

            return $this->sendSuccess($event);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
    }
}
