<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\DeliveryAssignment;
use Illuminate\Support\Facades\Log;

class Delivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'courier_id',
        'logistics_partner_id',
        'origin',
        'destination',
        'status',
        'type',
        'item_description',
        'estimated_weight',
        'dimensions',
        'value',
        'estimated_time',
        'confirmation_code',
        'current_position',
        'stages',
        'special_instructions',
        'payment_method',
        'is_hybrid'
    ];

    protected $casts = [
        'origin' => 'array',
        'destination' => 'array',
        'dimensions' => 'array',
        'value' => 'decimal:2',
        'estimated_time' => 'integer',
        'estimated_weight' => 'decimal:2',
        'current_position' => 'array',
        'stages' => 'array',
        'payment_method' => 'string',
        'is_hybrid' => 'boolean'
    ];

    public function setStagesAttribute($value)
    {
        if (is_array($value)) {
            foreach ($value as &$stage) {
                if (isset($stage['partner_id']) && !User::where('id', $stage['partner_id'])->exists()) {
                    throw new \InvalidArgumentException("Partner ID {$stage['partner_id']} does not exist");
                }
            }
            $this->attributes['stages'] = json_encode($value);
        } else {
            $this->attributes['stages'] = $value;
        }
    }

    public function getStagesAttribute($value)
    {
        if (is_null($value)) {
            return null;
        }
        return is_array($value) ? $value : json_decode($value, true);
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function courier()
    {
        return $this->belongsTo(User::class, 'courier_id');
    }

    public function logisticsPartner()
    {
        return $this->belongsTo(User::class, 'logistics_partner_id');
    }

    public function order()
    {
        return $this->hasOne(Order::class, 'delivery_id');
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class, 'delivery_id');
    }

    public function assignments()
    {
        return $this->hasMany(DeliveryAssignment::class);
    }

    protected static function booted()
    {
        static::created(function (Delivery $delivery) {
            if ($delivery->is_hybrid && $delivery->stages) {
                foreach ($delivery->stages as $index => $stage) {
                    DeliveryAssignment::create([
                        'delivery_id' => $delivery->id,
                        'partner_id' => $stage['partner_id'] ?? null,
                        'stage' => $index,
                        'status' => $stage['status'] ?? 'pending'
                    ]);
                }
            }
        });
    }
}
