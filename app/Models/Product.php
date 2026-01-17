<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['name', 'price', 'stock', 'image_path'];

    // Append custom attribute
    protected $appends = ['image_url'];

    public function getImageUrlAttribute()
    {
        if ($this->image_path) {
            return url('storage/' . $this->image_path);
        }
        return null;
    }
}