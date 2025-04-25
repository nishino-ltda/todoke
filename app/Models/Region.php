<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    use HasFactory;

    protected $fillable = [
        'partner_id',
        'name',
        'polygon',
        'status'
    ];

    protected $casts = [
        'polygon' => 'array'
    ];

    public function parceiro()
    {
        return $this->belongsTo(User::class, 'partner_id');
    }

    public function nodes()
    {
        return $this->hasMany(Node::class, 'region_id');
    }
}
