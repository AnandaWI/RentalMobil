<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MService extends Model
{
    protected $table = 'm_services';

    protected $fillable = [
        'name',
        'description',
    ];

    public function images()
    {
        return $this->hasMany(ServiceImage::class, 'service_id');
    }
}
