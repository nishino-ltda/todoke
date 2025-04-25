<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Node extends Model
{
    use HasFactory;

    protected $fillable = [
        'partner_id',
        'type',
        'identificador',
        'capacity',
        'status',
        'region_id',
        'current_position'
    ];

    protected $casts = [
        'current_position' => 'array',
        'capacity' => 'decimal:2'
    ];

    public function parceiro()
    {
        return $this->belongsTo(User::class, 'partner_id');
    }

    public function regiao()
    {
        return $this->belongsTo(Region::class, 'region_id');
    }

    public function deliveries()
    {
        return $this->hasMany(Delivery::class, 'node_id');
    }
}
