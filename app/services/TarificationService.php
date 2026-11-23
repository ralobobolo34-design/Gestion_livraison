<?php

namespace App\Services;

use App\Models\Tarif;
use App\Models\Zone;

class TarificationService
{
    // Suppléments appliqués quand aucun tarif spécifique n'existe pour le type/la priorité
    public const SUPPLEMENT_EXPRESS = 0.5;  // +50 %
    public const SUPPLEMENT_URGENTE = 0.3;  // +30 %

    /** Retourne le prix du colis, ou null si le poids est hors grille. */
    public function prixColis(Zone $zone, float $poids, string $type, string $priorite): ?int
    {
        // 1. Tarif exact (zone + poids + type + priorité)
        if ($exact = $this->trouver($zone->id, $poids, $type, $priorite)) {
            return $exact->prix;
        }

        // 2. Sinon : tarif standard/normal + suppléments
        $base = $this->trouver($zone->id, $poids, 'STANDARD', 'NORMALE');
        if (! $base) {
            return null;
        }

        $coef = 1;
        if ($type === 'EXPRESS') {
            $coef += self::SUPPLEMENT_EXPRESS;
        }
        if ($priorite === 'URGENTE') {
            $coef += self::SUPPLEMENT_URGENTE;
        }

        return (int) round($base->prix * $coef, -2); 
    }

    private function trouver(int $zoneId, float $poids, string $type, string $priorite): ?Tarif
    {
        return Tarif::where('zone_id', $zoneId)
            ->where('type_livraison', $type)
            ->where('priorite', $priorite)
            ->where('poids_min', '<', $poids)    
            ->where('poids_max', '>=', $poids)  
            ->first();
    }
}