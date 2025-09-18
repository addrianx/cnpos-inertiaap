<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockTransfer extends Model
{
    protected $fillable = [
        'from_store_id',
        'to_store_id',
        'product_id',
        'quantity',
        'reference',
        'note',
    ];

    public function fromStore() {
        return $this->belongsTo(Store::class, 'from_store_id');
    }

    public function toStore() {
        return $this->belongsTo(Store::class, 'to_store_id');
    }

    public function product() {
        return $this->belongsTo(Product::class);
    }
}
