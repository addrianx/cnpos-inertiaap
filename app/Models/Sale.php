<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        'user_id',
        'store_id',
        'subtotal',
        'discount',
        'total',
        'paid',
        'change',
        'sale_code',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($sale) {
            // ambil id terakhir + 1 untuk membuat nomor urut
            $lastId = self::max('id') + 1;

            // Format: SALE-20250917-0001
            $sale->sale_code = 'SALE-' . now()->format('Ymd') . '-' . str_pad($lastId, 4, '0', STR_PAD_LEFT);
        });
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
