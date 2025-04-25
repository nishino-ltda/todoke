<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    use HasFactory;

    protected $fillable = [
        'parceiroId',
        'name',
        'poligono',
        'status'
    ];

    protected $casts = [
        'poligono' => 'array'
    ];

    public function parceiro()
    {
        return $this->belongsTo(User::class, 'parceiroId');
    }

    public function nodes()
    {
        return $this->hasMany(Node::class, 'regiaoId');
    }
}
