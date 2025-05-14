<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarDestinationPrice extends Model
{
    protected $fillable = [
        'destination_id',
        'car_type_id',
        'price'
    ];

    // Relationship with MDestination
    public function destination()
    {
        return $this->belongsTo(MDestination::class, 'destination_id');
    }

    // Relationship with MCarType
    public function carType()
    {
        return $this->belongsTo(MCarType::class, 'car_type_id');
    }
}
