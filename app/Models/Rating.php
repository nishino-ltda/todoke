<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_id',
        'rater_id',
        'rated_id',
        'rating',
        'comment'
    ];

    public function entrega()
    {
        return $this->belongsTo(Delivery::class, 'delivery_id');
    }

    public function avaliador()
    {
        return $this->belongsTo(User::class, 'rater_id');
    }

    public function avaliado()
    {
        return $this->belongsTo(User::class, 'rated_id');
    }
}
