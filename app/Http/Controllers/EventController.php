<?php

namespace App\Http\Controllers;

use App\Http\Requests\EventStoreRequest;
use App\Models\Event;
use Faker\Provider\Base;
use Illuminate\Http\Request;

class EventController extends BaseController
{
    //
    public function index()
    {
        $data = Event::select(['id', 'subject', 'publish_date', 'is_published'])->get();
        return $this->sendSuccess($data);
    }

    public function show($id)
    {
        $data = Event::find($id);
        return $this->sendSuccess($data);
    }

    public function store(EventStoreRequest $request)
    {
        $data = $request->validated();
        $event = Event::create($data);
        return $this->sendSuccess($event);
    }

    public function update(EventStoreRequest $request, $id)
    {
        $data = $request->validated();
        $event = Event::findOrFail($id);
        $event->update($data);
        return $this->sendSuccess($event);
    }

    public function destroy($id)
    {
        $event = Event::findOrFail($id);
        $event->delete();
        return $this->sendSuccess($event);
    }
}
