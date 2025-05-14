<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OwnerCarAvailability extends Model
{
    protected $fillable = [
        'car_id',
        'not_available_at',
        'available_at'
    ];

    // Relationship with OwnerCar
    public function car()
    {
        return $this->belongsTo(OwnerCar::class, 'car_id');
    }
}
