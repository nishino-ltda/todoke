<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'courier_id',
        'origin',
        'destination',
        'status',
        'type',
        'item_description',
        'estimated_weight',
        'dimensions',
        'value',
        'estimated_time',
        'confirmation_code',
        'node_id',
        'current_position'
    ];

    protected $casts = [
        'origin' => 'array',
        'destination' => 'array',
        'dimensions' => 'array',
        'value' => 'decimal:2',
        'estimated_time' => 'integer',
        'estimated_weight' => 'decimal:2',
        'current_position' => 'array'
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function courier()
    {
        return $this->belongsTo(User::class, 'courier_id');
    }

    public function node()
    {
        return $this->belongsTo(Node::class, 'node_id');
    }

    public function order()
    {
        return $this->hasOne(Order::class, 'delivery_id');
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class, 'delivery_id');
    }
}
