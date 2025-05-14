<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MDestination extends Model
{
    protected $table = 'm_destinations';

    protected $fillable = [
        'name',
        'posibility_day'
    ];

    // Relationship with CarDestinationPrice
    public function carDestinationPrices()
    {
        return $this->hasMany(CarDestinationPrice::class, 'destination_id');
    }

    // Relationship with OrderDetail (since destinations are likely used in orders)
    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class, 'destination_id');
    }
}
