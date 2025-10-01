<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Store extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'address', 'user_id'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'store_user')->withTimestamps();
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function role()
    {
        // Ambil role dari user manager toko
        return $this->user ? $this->user->roles() : null;
    }
}
