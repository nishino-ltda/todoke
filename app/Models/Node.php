<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Node extends Model
{
    use HasFactory;

    protected $fillable = [
        'parceiroId',
        'tipo',
        'identificador',
        'capacidade',
        'status',
        'regiaoId',
        'posicaoAtual'
    ];

    protected $casts = [
        'posicaoAtual' => 'array',
        'capacidade' => 'decimal:2'
    ];

    public function parceiro()
    {
        return $this->belongsTo(User::class, 'parceiroId');
    }

    public function regiao()
    {
        return $this->belongsTo(Region::class, 'regiaoId');
    }

    public function deliveries()
    {
        return $this->hasMany(Delivery::class, 'nodeId');
    }
}
