<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistoryTransaction extends Model
{
    protected $fillable = [
        'owner_id',
        'transaction_type',
        'order_id',
        'amount',
        'balance_now'
    ];

    // Relationship with Owner
    public function owner()
    {
        return $this->belongsTo(Owner::class);
    }

    // Relationship with Order (nullable)
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
