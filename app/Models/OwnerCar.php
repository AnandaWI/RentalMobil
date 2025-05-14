<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OwnerCar extends Model
{
    protected $fillable = [
        'car_type_id',
        'owner_id',
        'plate_number'
    ];

    // Relationship with MCarType
    public function carType()
    {
        return $this->belongsTo(MCarType::class, 'car_type_id');
    }

    // Relationship with Owner
    public function owner()
    {
        return $this->belongsTo(Owner::class);
    }

    // Relationship with OrderDetail
    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class, 'car_id');
    }

    // Relationship with OwnerCarAvailability
    public function availabilities()
    {
        return $this->hasMany(OwnerCarAvailability::class, 'car_id');
    }
}
