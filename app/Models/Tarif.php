<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tarif extends Model
{
    protected $fillable = [
        'zone_id', 'poids_min', 'poids_max',
        'type_livraison', 'priorite', 'prix',
    ];

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }
}