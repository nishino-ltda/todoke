<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'deliveryId',
        'parceiroId',
        'etapa',
        'status'
    ];

    public function delivery()
    {
        return $this->belongsTo(Delivery::class, 'deliveryId');
    }

    public function partner()
    {
        return $this->belongsTo(User::class, 'parceiroId');
    }
}
