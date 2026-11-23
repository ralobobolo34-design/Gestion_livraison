<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tournee extends Model
{
    protected $fillable = [
        'reference', 'date', 'livreur_id',
        'heure_depart', 'heure_fin', 'statut',
    ];

    protected $casts = ['date' => 'date'];

    public const TRANSITIONS = [
        'PLANIFIEE' => ['EN_COURS', 'ANNULEE'],
        'EN_COURS'  => ['TERMINEE', 'ANNULEE'],
        'TERMINEE'  => [],
        'ANNULEE'   => [],
    ];

    // Une tournée "active" occupe le livreur (RM10) et ses colis
    public const ACTIVES = ['PLANIFIEE', 'EN_COURS'];

    // Statuts de colis qu'on peut affecter à une tournée
    public const COLIS_AFFECTABLES = ['ARRIVE_AGENCE', 'ECHEC_LIVRAISON', 'EN_LIVRAISON'];

    public function livreur(): BelongsTo
    {
        return $this->belongsTo(Livreur::class);
    }

    public function colis(): BelongsToMany
    {
        return $this->belongsToMany(Colis::class, 'tournee_details')
                    ->withPivot('ordre')
                    ->withTimestamps();
    }

    public function statutsSuivants(): array
    {
        return self::TRANSITIONS[$this->statut] ?? [];
    }
}