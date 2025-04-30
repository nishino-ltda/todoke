<?php

namespace App\Repositories;

use App\Models\VotingRound;

class VotingRoundRepository implements VotingRoundRepositoryInterface
{
    public function findWithRelations(int $id, array $relations): VotingRound
    {
        return VotingRound::with($relations)->findOrFail($id);
    }
}
