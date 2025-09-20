<?php

// app/Models/StockLoan.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockLoan extends Model
{
    protected $fillable = [
        'from_store_id','to_store_id','loan_date','status','notes'
    ];

    public function fromStore() {
        return $this->belongsTo(Store::class, 'from_store_id');
    }

    public function toStore() {
        return $this->belongsTo(Store::class, 'to_store_id');
    }

    public function items() {
        return $this->hasMany(StockLoanItem::class);
    }
}
