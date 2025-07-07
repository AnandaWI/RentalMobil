<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MCarType extends Model
{
    protected $table = 'm_car_types';

    protected $fillable = [
        'category_id',
        'car_name',
        'capacity',
        'rent_price',
        'description'
    ];

    // Relationship with MCarCategory
    public function category()
    {
        return $this->belongsTo(MCarCategory::class, 'category_id');
    }

    public function images()
    {
        return $this->hasMany(CarTypeImage::class, 'car_type_id');
    }

    // Relationship with OwnerCar
    public function ownerCars()
    {
        return $this->hasMany(OwnerCar::class, 'car_type_id');
    }

    public function features() {
        return $this->hasMany(CarTypeFeature::class, 'car_type_id');
    }
}
