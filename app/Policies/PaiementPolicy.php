<?php

namespace App\Policies;

use App\Models\Paiement;
use App\Models\User;

class PaiementPolicy
{
    // Le client retire sa propre déclaration, tant qu'elle n'est pas confirmée
    public function retirer(User $user, Paiement $paiement): bool
    {
        return $user->isClient()
            && $user->client !== null
            && $paiement->commande->client_id === $user->client->id
            && $paiement->statut === 'EN_ATTENTE';
    }
}