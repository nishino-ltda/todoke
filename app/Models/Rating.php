<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Rating extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'entregaId',
        'avaliadorId',
        'avaliadoId',
        'nota',
        'comentario'
    ];

    public function entrega()
    {
        return $this->belongsTo(Delivery::class, 'entregaId');
    }

    public function avaliador()
    {
        return $this->belongsTo(User::class, 'avaliadorId');
    }

    public function avaliado()
    {
        return $this->belongsTo(User::class, 'avaliadoId');
    }
}
