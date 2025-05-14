<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MBank extends Model
{
    protected $table = 'm_banks';

    protected $fillable = [
        'code',
        'name',
        'logo'
    ];

    // Based on the project structure, MBank might have relationships with:
    // - HistoryTransaction (if bank is used for transactions)
    public function historyTransactions()
    {
        return $this->hasMany(HistoryTransaction::class, 'bank_id');
    }
}
