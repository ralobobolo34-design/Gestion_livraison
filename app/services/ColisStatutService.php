<?php

namespace App\Services;

use App\Models\Colis;
use App\Models\HistoriqueStatut;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ColisStatutService
{
    // Libellés affichés au public
public const LIBELLES = [
    'CREE'            => 'Commande enregistrée',
    'PRIS_EN_CHARGE'  => 'Colis pris en charge',
    'EN_TRANSIT'      => 'En transit',
    'ARRIVE_AGENCE'   => "Arrivé à l'agence",
    'EN_LIVRAISON'    => 'En cours de livraison',
    'LIVRE'           => 'Livré',
    'ECHEC_LIVRAISON' => 'Échec de livraison',
    'ANNULE'          => 'Annulé',
];

// Étapes du cycle normal, dans l'ordre
public const ETAPES = ['CREE', 'PRIS_EN_CHARGE', 'EN_TRANSIT', 'ARRIVE_AGENCE', 'EN_LIVRAISON', 'LIVRE'];
    // Cycle normal : CREE → PRIS_EN_CHARGE → EN_TRANSIT → ARRIVE_AGENCE → EN_LIVRAISON → LIVRE
    public const TRANSITIONS = [
        'CREE'            => ['PRIS_EN_CHARGE', 'ANNULE'],
        'PRIS_EN_CHARGE'  => ['EN_TRANSIT', 'ANNULE'],
        'EN_TRANSIT'      => ['ARRIVE_AGENCE', 'ANNULE'],
        'ARRIVE_AGENCE'   => ['EN_LIVRAISON', 'ANNULE'],
        'EN_LIVRAISON'    => ['LIVRE', 'ECHEC_LIVRAISON'],
        'ECHEC_LIVRAISON' => ['EN_LIVRAISON', 'ANNULE'],
        'LIVRE'           => [],   // RM05 : plus aucun retour possible
        'ANNULE'          => [],
    ];

    public static function suivants(string $statut): array
    {
        return self::TRANSITIONS[$statut] ?? [];
    }

    public function initialiser(Colis $colis, ?int $userId): void
    {
        HistoriqueStatut::create([
            'colis_id'       => $colis->id,
            'user_id'        => $userId,
            'ancien_statut'  => null,
            'nouveau_statut' => $colis->statut,
        ]);
    }

    public function changer(Colis $colis, string $nouveau, ?int $userId): void
    {
        if (! in_array($nouveau, self::suivants($colis->statut), true)) {
            throw ValidationException::withMessages([
                'statut' => "Passage de {$colis->statut} à {$nouveau} interdit pour le colis {$colis->reference}.",
            ]);
        }

        DB::transaction(function () use ($colis, $nouveau, $userId) {
            $ancien = $colis->statut;
            $colis->update(['statut' => $nouveau]);

            HistoriqueStatut::create([   // RM06
                'colis_id'       => $colis->id,
                'user_id'        => $userId,
                'ancien_statut'  => $ancien,
                'nouveau_statut' => $nouveau,
            ]);
        });
    }
}