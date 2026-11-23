<?php

namespace App\Policies;

use App\Models\Colis;
use App\Models\User;

class ColisPolicy
{
    public function view(User $user, Colis $colis): bool
    {
        if ($user->isStaff()) {
            return true;
        }

        return $user->isClient()
            && $user->client !== null
            && $user->client->id === $colis->commande->client_id;
    }
}