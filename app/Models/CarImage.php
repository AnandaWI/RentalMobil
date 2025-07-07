<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MCarType extends Model
{
    protected $table = 'car_images';

    protected $fillable = [
        'car_id',
        'img_url',
    ];
}
