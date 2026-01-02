<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    protected $fillable = ['member_code', 'name', 'status', 'current_expiry_date'];

    // Relasi: Satu member bisa punya banyak transaksi
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}