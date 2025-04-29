<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Addon extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'partner_id',
        'name',
        'description',
        'price',
        'status'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'status' => 'string'
    ];

    public function partner()
    {
        return $this->belongsTo(User::class, 'partner_id');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_addon');
    }
}
