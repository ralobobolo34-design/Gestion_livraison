<?php

namespace App\Models;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Colis extends Model
{
    use HasFactory;
    protected $table = 'colis';

    protected $fillable = [
        'reference', 'commande_id', 'description', 'poids',
        'dimensions', 'valeur_declaree', 'fragile', 'prix', 'statut',
    ];

    protected $casts = ['fragile' => 'boolean'];

    public function commande(): BelongsTo
    {
        return $this->belongsTo(Commande::class);
    }

    public function tournees(): BelongsToMany
    {
        return $this->belongsToMany(Tournee::class, 'tournee_details')
                    ->withPivot('ordre')
                    ->withTimestamps();
    }

    public function livraisons(): HasMany
    {
        return $this->hasMany(Livraison::class);
    }

    public function historiques(): HasMany
    {
        return $this->hasMany(HistoriqueStatut::class);
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