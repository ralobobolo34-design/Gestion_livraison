<?php

namespace App\Services;

use App\Models\Colis;
use App\Models\Livreur;
use App\Models\Tournee;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TourneeService
{
    public function __construct(
        private ReferenceService $references,
        private ColisStatutService $colisStatuts,
    ) {}

    public function creer(array $data): Tournee
    {
        return DB::transaction(function () use ($data) {
            // Verrou sur le livreur : deux créations simultanées ne peuvent pas se chevaucher (RM10)
            $livreur = Livreur::lockForUpdate()->findOrFail($data['livreur_id']);

            $this->verifierLivreur($livreur, 'livreur_id');              // RM02
            $this->verifierDisponibilite($livreur, $data['date'], $data['heure_depart'], $data['heure_fin']); // RM10

            return Tournee::create([
                'reference'    => $this->references->generer('TRN', 'tournees'),
                'date'         => $data['date'],
                'livreur_id'   => $livreur->id,
                'heure_depart' => $data['heure_depart'],
                'heure_fin'    => $data['heure_fin'],
                'statut'       => 'PLANIFIEE',
            ]);
        });
    }

    public function ajouterColis(Tournee $tournee, int $colisId): void
    {
        DB::transaction(function () use ($tournee, $colisId) {
            $tournee = Tournee::with('livreur')->lockForUpdate()->findOrFail($tournee->id);

            // RM03 : tournée valide
            if ($tournee->statut !== 'PLANIFIEE') {
                $this->erreur('colis_id', 'On ne peut ajouter des colis qu\'à une tournée planifiée.');
            }

            // RM02
            $this->verifierLivreur($tournee->livreur, 'colis_id');

            $colis = Colis::with('commande')->findOrFail($colisId);

            // RM04 : pas deux fois dans la même tournée
            if ($tournee->colis()->whereKey($colis->id)->exists()) {
                $this->erreur('colis_id', "Le colis {$colis->reference} est déjà dans cette tournée.");
            }

            // RM09
            if ($colis->commande->statut === 'ANNULEE') {
                $this->erreur('colis_id', "Le colis {$colis->reference} appartient à une commande annulée.");
            }

            if (! in_array($colis->statut, Tournee::COLIS_AFFECTABLES, true)) {
                $this->erreur('colis_id', "Le colis {$colis->reference} ({$colis->statut}) ne peut pas être affecté : il doit être arrivé à l'agence.");
            }

            // Un colis ne peut être que dans une seule tournée active
            if ($colis->tournees()->whereIn('tournees.statut', Tournee::ACTIVES)->exists()) {
                $this->erreur('colis_id', "Le colis {$colis->reference} est déjà affecté à une autre tournée active.");
            }

            $ordre = (int) $tournee->colis()->max('tournee_details.ordre') + 1;
            $tournee->colis()->attach($colis->id, ['ordre' => $ordre]);
        });
    }

    public function retirerColis(Tournee $tournee, Colis $colis): void
    {
        if ($tournee->statut !== 'PLANIFIEE') {
            $this->erreur('colis_id', 'On ne peut retirer un colis que d\'une tournée planifiée.');
        }

        $tournee->colis()->detach($colis->id);
    }

    public function changerStatut(Tournee $tournee, string $nouveau, ?int $userId): void
    {
        if (! in_array($nouveau, $tournee->statutsSuivants(), true)) {
            $this->erreur('statut', "Passage de {$tournee->statut} à {$nouveau} interdit.");
        }

        DB::transaction(function () use ($tournee, $nouveau, $userId) {
            $tournee->load('colis.commande', 'livreur');

            if ($nouveau === 'EN_COURS') {
                $this->demarrer($tournee, $userId);
            }

            if ($nouveau === 'TERMINEE') {
                $restants = $tournee->colis->filter(fn (Colis $c) => $c->statut === 'EN_LIVRAISON');

                if ($restants->isNotEmpty()) {
                    $this->erreur('statut', 'Impossible de terminer : ' . $restants->count()
                        . ' colis sont encore en livraison (livré ou échec à saisir).');
                }
            }

            $tournee->update(['statut' => $nouveau]);
        });
    }

    private function demarrer(Tournee $tournee, ?int $userId): void
    {
        if ($tournee->colis->isEmpty()) {
            $this->erreur('statut', 'Ajoutez au moins un colis avant de démarrer la tournée.');
        }

        $this->verifierLivreur($tournee->livreur, 'statut'); // RM02

        foreach ($tournee->colis as $colis) {
            if ($colis->commande->statut === 'ANNULEE') {    // RM09
                $this->erreur('statut', "Le colis {$colis->reference} appartient à une commande annulée : retirez-le.");
            }
        }

        foreach ($tournee->colis as $colis) {
            if (in_array($colis->statut, ['ARRIVE_AGENCE', 'ECHEC_LIVRAISON'], true)) {
                $this->colisStatuts->changer($colis, 'EN_LIVRAISON', $userId);
            }
        }

        // Les commandes « en préparation » passent « en livraison » avec le départ de la tournée
        foreach ($tournee->colis->pluck('commande')->unique('id') as $commande) {
            if ($commande->statut === 'EN_PREPARATION') {
                $commande->update(['statut' => 'EN_LIVRAISON']);
            }
        }
    }

    // RM02
    private function verifierLivreur(Livreur $livreur, string $champ): void
    {
        if (! $livreur->peutRecevoirAffectation()) {
            $this->erreur($champ, "Le livreur {$livreur->nom} {$livreur->prenom} est {$livreur->statut} : il ne peut pas recevoir d'affectation.");
        }
    }

    // RM10 : deux intervalles se chevauchent si début1 < fin2 ET fin1 > début2
    private function verifierDisponibilite(Livreur $livreur, string $date, string $debut, string $fin): void
    {
        $conflit = Tournee::where('livreur_id', $livreur->id)
            ->whereDate('date', $date)
            ->whereIn('statut', Tournee::ACTIVES)
            ->where('heure_depart', '<', $fin)
            ->where('heure_fin', '>', $debut)
            ->first();

        if ($conflit) {
            $this->erreur('heure_depart', "Ce livreur a déjà la tournée {$conflit->reference} le même jour de "
                . substr($conflit->heure_depart, 0, 5) . ' à ' . substr($conflit->heure_fin, 0, 5) . '.');
        }
    }

    private function erreur(string $champ, string $message): never
    {
        throw ValidationException::withMessages([$champ => $message]);
    }
}