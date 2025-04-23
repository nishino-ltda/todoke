<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Product extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'restauranteId',
        'nome',
        'descricao',
        'preco',
        'categoria',
        'imagemUrl',
        'status'
    ];

    protected $casts = [
        'preco' => 'decimal:2',
        'status' => 'string'
    ];

    public function restaurante()
    {
        return $this->belongsTo(User::class, 'restauranteId');
    }

    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_items')
            ->withPivot('quantidade', 'precoUnitario');
    }
}
