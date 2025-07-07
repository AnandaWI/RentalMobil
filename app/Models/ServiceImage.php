<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceImage extends Model
{
    protected $table = 'service_images';

    protected $fillable = [
        'service_id',
        'img_url',
    ];

    public function service() {
        return $this->belongsTo(MService::class, 'service_id');
    }
}
