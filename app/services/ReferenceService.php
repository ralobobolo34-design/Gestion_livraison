<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class ReferenceService
{
    /** Exemple : generer('COL', 'colis') donne COL-2026-00001, puis COL-2026-00002... */
    public function generer(string $prefixe, string $table): string
    {
        $debut = $prefixe . '-' . now()->year . '-';

        $derniere = DB::table($table)
            ->where('reference', 'like', $debut . '%')
            ->orderByDesc('reference')
            ->value('reference');

        $numero = $derniere ? ((int) substr($derniere, strlen($debut))) + 1 : 1;

        return $debut . str_pad($numero, 5, '0', STR_PAD_LEFT);
    }
}