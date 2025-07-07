<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MFeature extends Model
{
    protected $table = 'm_features';

    protected $fillable = [
        'name',
        'description',
        'img_url',
    ];
}
