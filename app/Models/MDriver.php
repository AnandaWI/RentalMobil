<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MDriver extends Model
{
    protected $table = 'm_drivers';

    protected $fillable = [
        'name',
        'img_url'
    ];
}
