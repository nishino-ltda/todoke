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
        'identifier',
        'capacity',
        'status',
        'region_id',
        'current_position'
    ];

    protected $casts = [
        'current_position' => 'array',
        'capacity' => 'decimal:2'
    ];

    public function partner()
    {
        return $this->belongsTo(User::class, 'partner_id');
    }

    public function region()
    {
        return $this->belongsTo(Region::class, 'region_id');
    }

    public function deliveries()
    {
        return $this->hasMany(Delivery::class, 'node_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'node_id');
    }
}
