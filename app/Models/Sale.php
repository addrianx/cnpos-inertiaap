<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

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
        'sale_date',
        'is_returned',
        'returned_at',
        'return_reason',
        'returned_by',
    ];

    protected $casts = [
        'sale_date' => 'date',
        'created_at' => 'datetime',
        'returned_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($sale) {
            if (empty($sale->sale_date)) {
                $sale->sale_date = now()->toDateString();
            }

            $lastId = self::max('id') + 1;
            $sale->sale_code = 'SALE-' . now()->format('Ymd') . '-' . str_pad($lastId, 4, '0', STR_PAD_LEFT);
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function returnedBy()
    {
        return $this->belongsTo(User::class, 'returned_by');
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    // ✅ Method untuk cek apakah bisa di-retur (dalam 3 hari)
    public function canBeReturned()
    {
        if ($this->is_returned) {
            return false; // Sudah di-retur
        }

        $saleDate = $this->sale_date ? Carbon::parse($this->sale_date) : $this->created_at;
        $returnDeadline = $saleDate->addDays(3);
        
        return now()->lte($returnDeadline);
    }

    // ✅ Method untuk mendapatkan sisa waktu retur
    public function getRemainingReturnTime()
    {
        if ($this->is_returned) {
            return 'Sudah di-retur';
        }

        $saleDate = $this->sale_date ? Carbon::parse($this->sale_date) : $this->created_at;
        $returnDeadline = $saleDate->addDays(3);
        
        if (now()->gt($returnDeadline)) {
            return 'Batas waktu retur habis';
        }

        return $returnDeadline->diffForHumans(now(), ['syntax' => Carbon::DIFF_ABSOLUTE]);
    }
}