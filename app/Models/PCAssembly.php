<?php
// app/Models/PCAssembly.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PCAssembly extends Model
{
    protected $table = 'pc_assemblies';
    use HasFactory;

    protected $fillable = [
        'name',
        'description', 
        'total_price',
        'components',
        'user_id',
        'store_id'
    ];

    protected $casts = [
        'components' => 'array',
        'total_price' => 'decimal:2'
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class)->select(['id', 'name', 'email']);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}