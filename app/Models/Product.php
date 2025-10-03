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
        'created_by',
    ];

    // ✅ TAMBAHKAN APPENDS YANG DIPERLUKAN
    protected $appends = [
        'stock', 
        'formatted_price', 
        'final_price', 
        'total_stock', 
        'has_stock', 
        'description'
    ];

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

    // ✅ ACCESSOR BARU YANG DIPERLUKAN
    public function getFormattedPriceAttribute()
    {
        return number_format($this->price, 0, ',', '.');
    }

    public function getFinalPriceAttribute()
    {
        $discountAmount = $this->price * ($this->discount / 100);
        return $this->price - $discountAmount;
    }

    public function getTotalStockAttribute()
    {
        return $this->stock; // Gunakan existing stock attribute
    }

    public function getHasStockAttribute()
    {
        return $this->total_stock > 0;
    }

    public function getDescriptionAttribute()
    {
        return "SKU: {$this->sku}";
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
    
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}