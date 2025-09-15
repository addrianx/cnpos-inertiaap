<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku',
        'name',
        'cost',
        'price',
        'discount',
    ];

    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
