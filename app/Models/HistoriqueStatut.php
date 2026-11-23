<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistoriqueStatut extends Model
{
    protected $table = 'historique_statuts';

    protected $fillable = [
        'colis_id', 'user_id', 'ancien_statut', 'nouveau_statut', 'created_at',
    ];

    public function colis(): BelongsTo
    {
        return $this->belongsTo(Colis::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}