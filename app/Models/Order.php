<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'clientId',
        'restaurantId', 
        'status',
        'totalValue',
        'deliveryId'
    ];

    protected $casts = [
        'totalValue' => 'decimal:2',
        'status' => 'string'
    ];

    public function client()
    {
        return $this->belongsTo(User::class, 'clientId');
    }

    public function restaurant()
    {
        return $this->belongsTo(User::class, 'restaurantId');
    }

    public function delivery()
    {
        return $this->belongsTo(Delivery::class, 'deliveryId');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'order_items')
            ->withPivot('quantity', 'unitPrice');
    }
    
    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }
}
