<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VotingRound extends Model
{
    use HasFactory;

    protected $fillable = [
        'start_time',
        'end_time',
        'status',
        'region_id',
    ];

    /**
     * Get the region that the voting round belongs to.
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    /**
     * Get the voting options for the voting round.
     */
    public function votingOptions(): HasMany
    {
        return $this->hasMany(VotingOption::class);
    }

    /**
     * Get the votes for the voting round.
     */
    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }
}
