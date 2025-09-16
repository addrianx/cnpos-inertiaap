<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Sale extends Model
{
    protected $fillable = [
        'user_id',
        'store_id',   // ✅ tambahkan
        'subtotal',
        'discount',
        'total',
        'paid',
        'change',
    ];

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
