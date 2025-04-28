<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vote extends Model
{
    protected $fillable = [
        'voting_round_id',
        'user_id',
        'ranked_options',
    ];

    protected $casts = [
        'ranked_options' => 'json',
    ];

    /**
     * Get the voting round that the vote belongs to.
     */
    public function votingRound(): BelongsTo
    {
        return $this->belongsTo(VotingRound::class);
    }

    /**
     * Get the user who cast the vote.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
