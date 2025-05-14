<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'name',
        'address',
        'phone_number',
        'email'
    ];

    // Relationship with Order (a customer can have many orders)
    public function orders()
    {
        return $this->hasMany(Order::class, 'customer_id');
    }

    // Relationship with HistoryTransaction
    public function historyTransactions()
    {
        return $this->hasMany(HistoryTransaction::class, 'customer_id');
    }
}
