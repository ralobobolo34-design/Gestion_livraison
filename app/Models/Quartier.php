<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quartier extends Model
{
    protected $table = 'quartiers';

    protected $fillable = ['zone_id', 'nom'];

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function commandes(): HasMany
    {
        return $this->hasMany(Commande::class);
    }
}