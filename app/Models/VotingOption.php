<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VotingOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'voting_round_id',
        'min_fare_per_km',
        'avg_fare_per_km',
        'max_fare_per_km',
    ];

    /**
     * Get the voting round that the option belongs to.
     */
    public function votingRound(): BelongsTo
    {
        return $this->belongsTo(VotingRound::class);
    }
}
