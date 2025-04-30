<?php

namespace App\Policies;

use App\Models\Node;
use App\Models\User;

class NodePolicy
{
    /**
     * Determine if the user can approve the node.
     */
    public function approve(User $user, Node $node): bool
    {
        return $user->type === 'admin';
    }
}
