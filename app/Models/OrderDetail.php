<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    protected $fillable = [
        'order_id',
        'car_id',
        'driver_id',
        'amount'
    ];

    // Relationship with Order
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // Relationship with OwnerCar
    public function car()
    {
        return $this->belongsTo(OwnerCar::class, 'car_id');
    }

    public function driver()
    {
        return $this->belongsTo(MDriver::class);
    }
}
