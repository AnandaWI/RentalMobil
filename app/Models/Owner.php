<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Owner extends Model
{
    protected $fillable = [
        'user_id',
        'balance',
        'bank_id',
        'bank_no'
    ];

    // Relationship with User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relationship with Bank
    public function bank()
    {
        return $this->belongsTo(MBank::class, 'bank_id');
    }

    // Relationship with OwnerCar
    public function ownerCars()
    {
        return $this->hasMany(OwnerCar::class);
    }

    // Relationship with HistoryTransaction
    public function historyTransactions()
    {
        return $this->hasMany(HistoryTransaction::class);
    }
}
