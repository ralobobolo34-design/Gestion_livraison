<?php

namespace App\Models;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Paiement extends Model
{
       protected $fillable = [
    'reference', 'commande_id', 'user_id',
    'montant', 'mode', 'operateur', 'reference_transaction',
    'date_paiement', 'statut',
];
    protected $casts = ['date_paiement' => 'datetime'];

    public function commande(): BelongsTo
    {
        return $this->belongsTo(Commande::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function scopePourUtilisateur(Builder $query, User $user): Builder
{
    if ($user->isStaff()) {
        return $query;
    }

    if ($user->isClient() && $user->client) {
        return $query->whereHas('commande', fn ($c) => $c->where('client_id', $user->client->id));
    }

    return $query->whereRaw('1 = 0');
}
 
}