<?php

// app/Models/StockLoanItem.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockLoanItem extends Model
{
    protected $fillable = [
        'stock_loan_id','product_id','quantity'
    ];

    public function loan() {
        return $this->belongsTo(StockLoan::class, 'stock_loan_id');
    }

    public function product() {
        return $this->belongsTo(Product::class);
    }
}
