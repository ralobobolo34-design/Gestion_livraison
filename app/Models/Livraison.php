<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Livraison extends Model
{
    protected $table = 'livraisons';

    protected $fillable = [
        'colis_id', 'livreur_id', 'date_livraison', 'nom_receptionnaire',
        'commentaire', 'motif_echec', 'signature', 'photo',
        'latitude', 'longitude',
    ];

    protected $casts = ['date_livraison' => 'datetime'];

    public function colis(): BelongsTo
    {
        return $this->belongsTo(Colis::class);
    }

    public function livreur(): BelongsTo
    {
        return $this->belongsTo(Livreur::class);
    }
    public const MOTIFS = [
    'CLIENT_ABSENT', 'ADRESSE_INTROUVABLE', 'CLIENT_REFUSE',
    'TELEPHONE_INJOIGNABLE', 'AUTRE',
];
}