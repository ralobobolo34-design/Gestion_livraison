<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Livreur extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id', 'nom', 'prenom', 'telephone', 'email',
        'type_vehicule', 'numero_vehicule', 'statut', 'date_inscription',
    ];

    protected $casts = ['date_inscription' => 'date'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tournees(): HasMany
    {
        return $this->hasMany(Tournee::class);
    }

    public function livraisons(): HasMany
    {
        return $this->hasMany(Livraison::class);
    }

    // Utile pour la règle RM02
    public function peutRecevoirAffectation(): bool
    {
        return $this->statut === 'ACTIF';
    }
}