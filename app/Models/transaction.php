<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class transaction extends Model
{
    protected $guarded = [];

    // Relasi: Satu transaksi punya banyak detail barang
    public function details()
    {
        return $this->hasMany(TransactionDetail::class,'transaction_id');
    }
}
