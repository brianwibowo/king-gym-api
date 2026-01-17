<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = ['user_id', 'member_id', 'customer_name', 'total_amount', 'payment_method', 'transaction_type'];

    // Relasi: Satu transaksi punya banyak detail barang/paket yang dibeli
    public function details()
    {
        return $this->hasMany(TransactionDetail::class);
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}