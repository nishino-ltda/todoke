<?php

namespace App\Repositories;

use App\Models\VotingRound;
use Illuminate\Database\Eloquent\Collection;

interface VotingRoundRepositoryInterface
{
    public function findWithRelations(int $id, array $relations): VotingRound;
}
