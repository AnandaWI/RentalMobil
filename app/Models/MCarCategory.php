<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MCarCategory extends Model
{
    protected $table = 'm_car_categories';

    protected $fillable = [
        'name'
    ];

    // Relationship with OwnerCar
    public function ownerCars()
    {
        return $this->hasMany(OwnerCar::class, 'car_category_id');
    }
}
