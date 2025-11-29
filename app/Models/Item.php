<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
    'id',
    'category',
    'name',
    'price',
    'stock_availability',
    'min_qty',
    'status',
    'expiration_date',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
