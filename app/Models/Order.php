<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Order extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'customer_id',
        'destination_id',
        'day',
        'total_price',
        'rent_date',
        // 'pick_up_time',
        'transaction_id',
        'status'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    // Relationship with Customer
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    // Relationship with Destination
    public function destination()
    {
        return $this->belongsTo(MDestination::class, 'destination_id');
    }

    // Relationship with OrderDetail
    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class);
    }

    // Relationship with HistoryTransaction
    public function historyTransaction()
    {
        return $this->hasOne(HistoryTransaction::class);
    }
}
