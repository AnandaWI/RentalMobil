<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarTypeImage extends Model
{
    //
    protected $fillable = ['car_type_id', 'img_url'];

    public function car_type()
    {
        return $this->belongsTo(MCarType::class, 'car_type_id');
    }
}
