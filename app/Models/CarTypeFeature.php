<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarTypeFeature extends Model
{
    protected $fillable = [
        'car_type_id',
        'feature'
    ];


    public function carType() {
        return $this->belongsTo(MCarType::class, 'car_type_id');
    }
}
