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
        'status',
        'community_min_fare_per_km',
        'community_avg_fare_per_km',
        'community_max_fare_per_km',
    ];

    protected $casts = [
        'polygon' => 'array',
        'community_min_fare_per_km' => 'decimal:2',
        'community_avg_fare_per_km' => 'decimal:2',
        'community_max_fare_per_km' => 'decimal:2',
    ];

    public function partner()
    {
        return $this->belongsTo(User::class, 'partner_id');
    }

    public function nodes()
    {
        return $this->hasMany(Node::class, 'region_id');
    }

    /**
     * Get the voting rounds for the region.
     */
    public function votingRounds()
    {
        return $this->hasMany(VotingRound::class);
    }
}
