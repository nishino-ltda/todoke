<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'partner_id', 
        'status',
        'total_value',
        'delivery_id'
    ];

    protected $casts = [
        'total_value' => 'decimal:2',
        'status' => 'string'
    ];

    public function client()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function partner()
    {
        return $this->belongsTo(User::class, 'partner_id');
    }

    public function delivery()
    {
        return $this->belongsTo(Delivery::class, 'delivery_id');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'order_items')
            ->withPivot('quantity', 'unit_price');
    }
    
    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }
}
