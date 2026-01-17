<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'user_id',
        'clock_in',
        'clock_out',
        'lat_in',
        'long_in',
        'lat_out',
        'long_out',
        'work_description',
        'photo_in',
        'photo_out'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
