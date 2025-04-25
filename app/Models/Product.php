<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'restaurantId',
        'name',
        'description',
        'price',
        'category',
        'imagemUrl',
        'status'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'status' => 'string'
    ];

    public function restaurant()
    {
        return $this->belongsTo(User::class, 'restaurantId');
    }

    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_items')
            ->withPivot('quantity', 'unitPrice');
    }
}
