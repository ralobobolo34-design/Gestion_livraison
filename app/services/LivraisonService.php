<?php

namespace App\Services;

use App\Models\Colis;
use App\Models\Commande;
use App\Models\Livraison;
use App\Models\Livreur;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LivraisonService
{
    private const PREFIXE_PNG = 'data:image/png;base64,';

    public function __construct(private ColisStatutService $colisStatuts) {}

    /** Confirme la livraison d'un colis, avec la preuve de livraison. */
    public function confirmer(Colis $colis, Livreur $livreur, array $data, ?UploadedFile $photo, ?int $userId): Livraison
    {
        return DB::transaction(function () use ($colis, $livreur, $data, $photo, $userId) {
            $colis = Colis::with('commande')->lockForUpdate()->findOrFail($colis->id);
            $this->verifier($colis, $livreur);

            $livraison = Livraison::create([
                'colis_id'           => $colis->id,
                'livreur_id'         => $livreur->id,
                'date_livraison'     => now(),
                'nom_receptionnaire' => $data['nom_receptionnaire'],
                'commentaire'        => $data['commentaire'] ?? null,
                'signature'          => $this->enregistrerSignature($data['signature'] ?? null),
                'photo'              => $photo?->store('preuves', 'public'),
                'latitude'           => $data['latitude'] ?? null,
                'longitude'          => $data['longitude'] ?? null,
            ]);

            $this->colisStatuts->changer($colis, 'LIVRE', $userId);   // RM05 et RM06
            $this->synchroniserCommande($colis->commande);

            return $livraison;
        });
    }

    /** Signale l'échec d'une livraison, avec son motif. */
    public function signalerEchec(Colis $colis, Livreur $livreur, array $data, ?int $userId): Livraison
    {
        return DB::transaction(function () use ($colis, $livreur, $data, $userId) {
            $colis = Colis::lockForUpdate()->findOrFail($colis->id);
            $this->verifier($colis, $livreur);

            $livraison = Livraison::create([
                'colis_id'       => $colis->id,
                'livreur_id'     => $livreur->id,
                'date_livraison' => now(),
                'motif_echec'    => $data['motif_echec'],
                'commentaire'    => $data['commentaire'] ?? null,
            ]);

            $this->colisStatuts->changer($colis, 'ECHEC_LIVRAISON', $userId);

            return $livraison;
        });
    }

    // Le colis doit être à livrer et appartenir à une tournée en cours de CE livreur
    private function verifier(Colis $colis, Livreur $livreur): void
    {
        $tournee = $colis->tournees()
            ->where('tournees.livreur_id', $livreur->id)
            ->where('tournees.statut', 'EN_COURS')
            ->first();

        if (! $tournee) {
            $this->erreur("Le colis {$colis->reference} n'est dans aucune de vos tournées en cours.");
        }

        if ($colis->statut !== 'EN_LIVRAISON') {
            $this->erreur("Le colis {$colis->reference} est « {$colis->statut} » : il n'est pas à livrer.");
        }
    }

    // Quand tous les colis (non annulés) sont livrés, la commande est livrée
    private function synchroniserCommande(Commande $commande): void
    {
        $commande->load('colis');
        $actifs = $commande->colis->where('statut', '!=', 'ANNULE');

        if ($commande->statut !== 'ANNULEE'
            && $actifs->isNotEmpty()
            && $actifs->every(fn (Colis $c) => $c->statut === 'LIVRE')) {
            $commande->update(['statut' => 'LIVREE']);
        }
    }

    // La signature arrive en PNG encodé (dessinée sur l'écran) : on la vérifie avant de l'enregistrer
    private function enregistrerSignature(?string $dataUrl): ?string
    {
        if (! $dataUrl || ! str_starts_with($dataUrl, self::PREFIXE_PNG)) {
            return null;
        }

        $binaire = base64_decode(substr($dataUrl, strlen(self::PREFIXE_PNG)), true);

        if ($binaire === false || strlen($binaire) > 300000 || ! str_starts_with($binaire, "\x89PNG")) {
            return null;
        }

        $chemin = 'signatures/' . Str::uuid() . '.png';
        Storage::disk('public')->put($chemin, $binaire);

        return $chemin;
    }

    private function erreur(string $message): never
    {
        throw ValidationException::withMessages(['statut' => $message]);
    }
}