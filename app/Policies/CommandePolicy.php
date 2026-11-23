<?php

namespace App\Policies;

use App\Models\Commande;
use App\Models\User;

class CommandePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff() || $user->isClient();
    }

    // Un client ne voit que ses propres commandes
    public function view(User $user, Commande $commande): bool
    {
        if ($user->isStaff()) {
            return true;
        }

        return $this->estLaSienne($user, $commande);
    }

    // Un client peut annuler sa commande tant qu'elle n'est pas confirmée
    public function annuler(User $user, Commande $commande): bool
    {
        return $this->estLaSienne($user, $commande) && $commande->statut === 'EN_ATTENTE';
    }

    // Un client ne paie que ses propres commandes
    public function payer(User $user, Commande $commande): bool
    {
        return $this->estLaSienne($user, $commande);
    }

    private function estLaSienne(User $user, Commande $commande): bool
    {
        return $user->isClient()
            && $user->client !== null
            && $user->client->id === $commande->client_id;
    }
}