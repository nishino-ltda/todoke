<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'clienteId',
        'restauranteId',
        'status',
        'valorTotal',
        'entregaId'
    ];

    protected $casts = [
        'valorTotal' => 'decimal:2',
        'status' => 'string'
    ];

    public function cliente()
    {
        return $this->belongsTo(User::class, 'clienteId');
    }

    public function restaurante()
    {
        return $this->belongsTo(User::class, 'restauranteId');
    }

    public function entrega()
    {
        return $this->belongsTo(Delivery::class, 'entregaId');
    }

    public function produtos()
    {
        return $this->belongsToMany(Product::class, 'order_items')
            ->withPivot('quantidade', 'precoUnitario');
    }
    
    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }
}
