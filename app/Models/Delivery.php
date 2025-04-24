<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Delivery extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'clienteId',
        'entregadorId',
        'origem',
        'destino',
        'status',
        'tipo',
        'descricaoItem',
        'pesoEstimado',
        'dimensoes',
        'valor',
        'tempoEstimado',
        'codigoConfirmacao',
        'nodeId',
        'posicaoAtual'
    ];

    protected $casts = [
        'origem' => 'array',
        'destino' => 'array',
        'dimensoes' => 'array',
        'valor' => 'decimal:2',
        'tempoEstimado' => 'integer',
        'pesoEstimado' => 'decimal:2',
        'posicaoAtual' => 'array'
    ];

    public function cliente()
    {
        return $this->belongsTo(User::class, 'clienteId');
    }

    public function entregador()
    {
        return $this->belongsTo(User::class, 'entregadorId');
    }

    public function node()
    {
        return $this->belongsTo(Node::class, 'nodeId');
    }

    public function order()
    {
        return $this->hasOne(Order::class, 'entregaId');
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class, 'entregaId');
    }
}
