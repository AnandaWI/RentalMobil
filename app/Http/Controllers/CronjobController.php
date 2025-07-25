<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class CronjobController extends BaseController
{
    //
    public function sendEventEmail()
    {
        $customers = Customer::select('email')
            ->whereHas('order', function ($query) {
                $query->where('status', 'success');
            })
            ->distinct()
            ->get();

        $events = Event::where('is_published', false)
            ->where('publish_date', '<=', now())
            ->get();

        foreach ($events as $event) {
            foreach ($customers as $customer) {
                Mail::to($customer->email)
                    ->send(new \App\Mail\EventMail($event->subject, $event->content, $event->link));
            }
            $event->update(['is_published' => true]);
        }

        // return $this->sendSuccess("success");
        return $this->sendSuccess([
            'customers' => $customers,
            'events' => $events,
            'now' => now(),
        ]);
    }
}
