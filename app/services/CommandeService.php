<?php

namespace App\Services;

use App\Models\Colis;
use App\Models\Commande;
use App\Models\Quartier;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CommandeService
{
    public function __construct(
        private TarificationService $tarifs,
        private ReferenceService $references,
        private ColisStatutService $colisStatuts,
    ) {}

    public function creer(array $data, ?int $userId): Commande
    {
        return DB::transaction(function () use ($data, $userId) {
            // La zone (donc le tarif) se déduit du quartier de destination
            $quartier = Quartier::with('zone')->findOrFail($data['quartier_id']);
            $zone     = $quartier->zone;

            if ($zone->statut !== 'ACTIF') {
                throw ValidationException::withMessages([
                    'quartier_id' => "La zone {$zone->nom} est inactive.",
                ]);
            }

            $commande = Commande::create([
                'reference'             => $this->references->generer('CMD', 'commandes'),
                'client_id'             => $data['client_id'],
                'zone_id'               => $zone->id,
                'quartier_id'           => $quartier->id,   // ← ligne manquante
                'user_id'               => $userId,
                'adresse_depart'        => $data['adresse_depart'],
                'adresse_destination'   => $data['adresse_destination'],
                'type_livraison'        => $data['type_livraison'],
                'priorite'              => $data['priorite'],
                'montant'               => 0,
                'date_commande'         => now()->toDateString(),
                'date_livraison_prevue' => $data['date_livraison_prevue'] ?? null,
                'statut'                => 'EN_ATTENTE',
            ]);

            $total = 0;

            foreach ($data['colis'] as $i => $ligne) {
                $poids = (float) $ligne['poids'];
                $prix  = $this->tarifs->prixColis($zone, $poids, $data['type_livraison'], $data['priorite']);

                if ($prix === null) {
                    throw ValidationException::withMessages([
                        "colis.$i.poids" => "Aucun tarif pour {$poids} kg dans la zone {$zone->nom}.",
                    ]);
                }

                $colis = Colis::create([
                    'reference'       => $this->references->generer('COL', 'colis'),
                    'commande_id'     => $commande->id,
                    'description'     => $ligne['description'] ?? null,
                    'poids'           => $poids,
                    'dimensions'      => $ligne['dimensions'] ?? null,
                    'valeur_declaree' => $ligne['valeur_declaree'] ?? null,
                    'fragile'         => ! empty($ligne['fragile']),
                    'prix'            => $prix,
                    'statut'          => 'CREE',
                ]);

                $this->colisStatuts->initialiser($colis, $userId);
                $total += $prix;
            }

            $commande->update(['montant' => $total]);

            return $commande;
        });
    }

    public function changerStatut(Commande $commande, string $nouveau, ?int $userId): void
    {
        if (! in_array($nouveau, $commande->statutsSuivants(), true)) {
            throw ValidationException::withMessages([
                'statut' => "Passage de {$commande->statut} à {$nouveau} interdit.",
            ]);
        }

        DB::transaction(function () use ($commande, $nouveau, $userId) {
            if ($nouveau === 'ANNULEE') {
                $commande->load('colis');

                // On vérifie d'abord que tous les colis sont annulables
                foreach ($commande->colis as $colis) {
                    if (! in_array('ANNULE', ColisStatutService::suivants($colis->statut), true)) {
                        throw ValidationException::withMessages([
                            'statut' => "Le colis {$colis->reference} ({$colis->statut}) ne peut plus être annulé.",
                        ]);
                    }
                }

                foreach ($commande->colis as $colis) {
                    $this->colisStatuts->changer($colis, 'ANNULE', $userId);
                }
            }

            $commande->update(['statut' => $nouveau]);
        });
    }
}