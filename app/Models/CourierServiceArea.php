<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourierServiceArea extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'polygon',
        'is_active',
    ];

    protected $casts = [
        'polygon' => 'array',
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
