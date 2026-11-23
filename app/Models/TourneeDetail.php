<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TourneeDetail extends Model
{
    protected $fillable = ['tournee_id', 'colis_id', 'ordre'];

    public function tournee(): BelongsTo
    {
        return $this->belongsTo(Tournee::class);
    }

    public function colis(): BelongsTo
    {
        return $this->belongsTo(Colis::class);
    }
}