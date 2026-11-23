<?php

namespace App\Models;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Commande extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference', 'client_id', 'zone_id', 'user_id',
        'adresse_depart', 'adresse_destination',
        'type_livraison', 'priorite', 'montant',
        'date_commande', 'date_livraison_prevue', 'statut',
    ];

    protected $casts = [
        'date_commande' => 'date',
        'date_livraison_prevue' => 'date',
    ];

    public const TRANSITIONS = [
        'EN_ATTENTE'     => ['CONFIRMEE', 'ANNULEE'],
        'CONFIRMEE'      => ['EN_PREPARATION', 'ANNULEE'],
        'EN_PREPARATION' => ['EN_LIVRAISON', 'ANNULEE'],
        'EN_LIVRAISON'   => ['LIVREE'],
        'LIVREE'         => [],
        'ANNULEE'        => [],
    ];
    public const LIBELLES = [
    'EN_ATTENTE'     => 'En attente de confirmation',
    'CONFIRMEE'      => 'Confirmée',
    'EN_PREPARATION' => 'En préparation',
    'EN_LIVRAISON'   => 'En cours de livraison',
    'LIVREE'         => 'Livrée',
    'ANNULEE'        => 'Annulée',
];

    // --- Relations ---

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function colis(): HasMany
    {
        return $this->hasMany(Colis::class);
    }

    public function paiements(): HasMany
    {
        return $this->hasMany(Paiement::class);
    }

    // --- Statuts ---

    public function statutsSuivants(): array
    {
        return self::TRANSITIONS[$this->statut] ?? [];
    }

    // --- Paiements ---

    // Argent réellement encaissé
    public function montantPaye(): int
    {
        return (int) $this->paiements()->whereIn('statut', ['PAYE', 'PARTIEL'])->sum('montant');
    }

    // Encaissé + paiements à la livraison encore en attente (sert à RM07)
    public function montantEngage(): int
    {
        return (int) $this->paiements()->whereIn('statut', ['PAYE', 'PARTIEL', 'EN_ATTENTE'])->sum('montant');
    }

    public function montantRestant(): int
    {
        return max(0, $this->montant - $this->montantEngage());
    }

    public function statutPaiement(): string
    {
        $paye = $this->montantPaye();

        if ($paye <= 0) {
            return 'NON_PAYE';
        }

        return $paye >= $this->montant ? 'PAYE' : 'PARTIEL';
    }
    public function quartier(): BelongsTo
{
    return $this->belongsTo(Quartier::class);
}
public function scopePourUtilisateur(Builder $query, User $user): Builder
{
    if ($user->isStaff()) {
        return $query;
    }

    if ($user->isClient() && $user->client) {
        return $query->where('client_id', $user->client->id);
    }

    return $query->whereRaw('1 = 0');
}
}