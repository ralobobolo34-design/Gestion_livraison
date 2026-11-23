<?php

namespace App\Policies;

use App\Models\Tournee;
use App\Models\User;

class TourneePolicy
{
    // Un livreur ne voit que ses propres tournées
    public function view(User $user, Tournee $tournee): bool
    {
        if ($user->isStaff()) {
            return true;
        }

        return $user->isLivreur()
            && $user->livreur !== null
            && $user->livreur->id === $tournee->livreur_id;
    }
}