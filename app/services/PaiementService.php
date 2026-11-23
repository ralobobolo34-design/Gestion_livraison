<?php

namespace App\Services;

use App\Models\Commande;
use App\Models\Paiement;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaiementService
{
    public function __construct(private ReferenceService $references) {}

    // Paiement enregistré par l'équipe
    public function enregistrer(array $data, ?int $userId): Paiement
    {
        return DB::transaction(function () use ($data, $userId) {
            // Verrou : deux paiements simultanés ne peuvent pas dépasser ensemble le montant
            $commande = Commande::lockForUpdate()->findOrFail($data['commande_id']);
            $this->verifierCommande($commande, 'commande_id');

            $montant = (int) $data['montant'];
            $restant = $commande->montantRestant();

            // RM07
            if ($montant > $restant) {
                throw ValidationException::withMessages([
                    'montant' => 'Le montant (' . number_format($montant, 0, ',', ' ') . ' Ar) dépasse le reste à régler ('
                        . number_format($restant, 0, ',', ' ') . ' Ar).',
                ]);
            }

            if ($data['mode'] === 'PAIEMENT_LIVRAISON') {
                $statut = 'EN_ATTENTE';
            } else {
                $statut = $montant === $restant ? 'PAYE' : 'PARTIEL';
            }

            return Paiement::create([
                'reference'     => $this->references->generer('PAY', 'paiements'),
                'commande_id'   => $commande->id,
                'user_id'       => $userId,
                'montant'       => $montant,
                'mode'          => $data['mode'],
                'date_paiement' => $data['date_paiement'] ?? now(),
                'statut'        => $statut,
            ]);
        });
    }

    // Paiement déclaré par le client : toujours « en attente » jusqu'à vérification par l'équipe
    public function declarer(Commande $commande, array $data, int $userId): Paiement
    {
        return DB::transaction(function () use ($commande, $data, $userId) {
            $commande = Commande::lockForUpdate()->findOrFail($commande->id);
            $this->verifierCommande($commande, 'montant');

            $montant = (int) $data['montant'];
            $restant = $commande->montantRestant();   // les paiements en attente comptent déjà

            // RM07
            if ($montant > $restant) {
                throw ValidationException::withMessages([
                    'montant' => 'Le montant (' . number_format($montant, 0, ',', ' ') . ' Ar) dépasse le reste à régler ('
                        . number_format($restant, 0, ',', ' ') . ' Ar).',
                ]);
            }

            return Paiement::create([
                'reference'             => $this->references->generer('PAY', 'paiements'),
                'commande_id'           => $commande->id,
                'user_id'               => $userId,
                'montant'               => $montant,
                'mode'                  => $data['mode'],
                'operateur'             => $data['operateur'] ?? null,
                'reference_transaction' => $data['reference_transaction'] ?? null,
                'date_paiement'         => now(),
                'statut'                => 'EN_ATTENTE',
            ]);
        });
    }

    // Un paiement en attente est encaissé (après vérification de la transaction ou remise des espèces)
    public function confirmer(Paiement $paiement): void
    {
        DB::transaction(function () use ($paiement) {
            $commande = Commande::lockForUpdate()->findOrFail($paiement->commande_id);
            $paiement->refresh();

            if ($paiement->statut !== 'EN_ATTENTE') {
                throw ValidationException::withMessages(['statut' => 'Seul un paiement en attente peut être confirmé.']);
            }
            $this->verifierCommande($commande, 'statut');

            $apres = $commande->montantPaye() + $paiement->montant;

            $paiement->update([
                'statut'        => $apres >= $commande->montant ? 'PAYE' : 'PARTIEL',
                'date_paiement' => now(),
            ]);
        });
    }

    public function annuler(Paiement $paiement): void
    {
        if ($paiement->statut === 'ANNULE') {
            throw ValidationException::withMessages(['statut' => 'Ce paiement est déjà annulé.']);
        }

        $paiement->update(['statut' => 'ANNULE']);
    }

    private function verifierCommande(Commande $commande, string $champ): void
    {
        if ($commande->statut === 'ANNULEE') {
            throw ValidationException::withMessages([$champ => 'Impossible de payer une commande annulée.']);
        }
        if ($commande->montant <= 0) {
            throw ValidationException::withMessages([$champ => 'Cette commande a un montant nul.']);
        }
    }
}