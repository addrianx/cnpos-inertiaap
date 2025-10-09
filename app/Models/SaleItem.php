<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'product_id',
        'item_type',
        'service_name',
        'service_description',
        'quantity',
        'price',
        'discount',
        'subtotal',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price' => 'decimal:2',
        'discount' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    // Relasi ke sale
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    // Relasi ke product (hanya untuk item_type = 'product')
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Accessor untuk nama item
    public function getItemNameAttribute()
    {
        return $this->item_type === 'product' 
            ? ($this->product ? $this->product->name : 'Produk tidak ditemukan')
            : $this->service_name;
    }

    // Scope untuk filter tipe item
    public function scopeProducts($query)
    {
        return $query->where('item_type', 'product');
    }

    public function scopeServices($query)
    {
        return $query->where('item_type', 'service');
    }
}