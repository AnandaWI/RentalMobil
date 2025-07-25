<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverAvailability extends Model
{
    protected $fillable = [
        'driver_id',
        'not_available_at',
        'available_at'
    ];

    // Relationship with OwnerCar
    public function car()
    {
        return $this->belongsTo(MDriver::class, 'driver_id');
    }
}
