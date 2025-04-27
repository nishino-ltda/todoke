<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'partner_id',
        'name',
        'description',
        'price',
        'category',
        'imageUrl',
        'status'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'status' => 'string'
    ];

    public function partner()
    {
        return $this->belongsTo(User::class, 'partner_id');
    }

    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_items')
            ->withPivot('quantity', 'unit_price');
    }
}
