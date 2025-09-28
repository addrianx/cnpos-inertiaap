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
        'store_id',
        'category_id',
    ];

    protected $appends = ['stock'];

    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }

    public function getStockAttribute()
    {
        $in         = $this->stocks()->where('type', 'in')->sum('quantity');
        $out        = $this->stocks()->where('type', 'out')->sum('quantity');
        $adjustment = $this->stocks()->where('type', 'adjustment')->sum('quantity');

        return $in + $adjustment - $out;
    }
    
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
