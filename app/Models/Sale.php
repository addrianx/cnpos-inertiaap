<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
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
}
