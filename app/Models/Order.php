<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = [
        'customer_id',
        'destination_id',
        'day',
        'total_price',
        'rent_date',
        'transaction_id',
        'status',
        'snap_token'
    ];

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
