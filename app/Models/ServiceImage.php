<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MCarType extends Model
{
    protected $table = 'service_images';

    protected $fillable = [
        'service_id',
        'img_url',
    ];
}
