<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MDriver extends Model
{
    protected $table = 'm_drivers';

    protected $fillable = [
        'name',
        'pengalaman',
        'tgl_lahir',
        'img_url'
    ];

    public function order_details()
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function availabilities()
    {
        return $this->hasMany(DriverAvailability::class);
    }
}
