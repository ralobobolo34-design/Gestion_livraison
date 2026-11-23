<?php

namespace App\Http\Controllers;

use App\Models\Colis;
use App\Models\Livraison;
use App\Models\Livreur;
use App\Models\Tournee;
use App\Services\LivraisonService;
use App\Services\TourneeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class EspaceLivreurController extends Controller
{
    public function index(Request $request)
    {
        $livreur = $this->livreur($request);

        $base = Tournee::where('livreur_id', $livreur->id)
            ->withCount([
                'colis',
                'colis as colis_livres_count' => fn ($q) => $q->where('colis.statut', 'LIVRE'),
            ]);

        $actives = (clone $base)->whereIn('statut', Tournee::ACTIVES)
            ->orderBy('date')->orderBy('heure_depart')->get();

        $historique = (clone $base)->whereNotIn('statut', Tournee::ACTIVES)
            ->latest('date')->take(10)->get();

        return view('espace_livreur.tournees.index', compact('livreur', 'actives', 'historique'));
    }

    public function show(Request $request, Tournee $tournee)
    {
        $this->livreur($request);
        Gate::authorize('view', $tournee);

        $tournee->load([
            'livreur',
            'colis.commande.client',
            'colis.commande.zone',
            'colis.commande.quartier',
            'colis.livraisons',
        ]);

        $peutDemarrer = $tournee->statut === 'PLANIFIEE' && ! $tournee->date->isFuture();
return view('espace_livreur.tournees.show', compact('tournee', 'peutDemarrer'));
    }

    public function changerStatut(Request $request, Tournee $tournee, TourneeService $service)
    {
        $this->livreur($request);
        Gate::authorize('view', $tournee);

        // Le livreur démarre et termine ; l'annulation reste au staff
        $data = $request->validate([
            'statut' => ['required', Rule::in(['EN_COURS', 'TERMINEE'])],
        ]);

        if ($data['statut'] === 'EN_COURS' && $tournee->date->isFuture()) {
            throw ValidationException::withMessages([
                'statut' => 'Cette tournée est prévue le ' . $tournee->date->format('d/m/Y') . ' : vous ne pouvez pas la démarrer avant.',
            ]);
        }

        $service->changerStatut($tournee, $data['statut'], $request->user()->id);

        return back()->with('success', $data['statut'] === 'EN_COURS' ? 'Tournée démarrée.' : 'Tournée terminée.');
    }

    public function livrer(Request $request, Colis $colis, LivraisonService $service)
    {
        $livreur = $this->livreur($request);

        $data = $request->validate([
            'nom_receptionnaire' => ['required', 'string', 'max:150'],
            'commentaire'        => ['nullable', 'string', 'max:500'],
            'photo'              => ['nullable', 'image', 'max:4096'],
            'signature'          => ['nullable', 'string', 'max:400000'],
            'latitude'           => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'          => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $service->confirmer($colis, $livreur, $data, $request->file('photo'), $request->user()->id);

        return back()->with('success', "Livraison du colis {$colis->reference} confirmée.");
    }

    public function echec(Request $request, Colis $colis, LivraisonService $service)
    {
        $livreur = $this->livreur($request);

        $data = $request->validate([
            'motif_echec' => ['required', Rule::in(Livraison::MOTIFS)],
            'commentaire' => ['nullable', 'required_if:motif_echec,AUTRE', 'string', 'max:500'],
        ], [
            'commentaire.required_if' => 'Précisez le motif dans le commentaire.',
        ]);

        $service->signalerEchec($colis, $livreur, $data, $request->user()->id);

        return back()->with('success', "Échec de livraison signalé pour {$colis->reference}.");
    }

    private function livreur(Request $request): Livreur
    {
        $livreur = $request->user()->livreur;

        abort_if(! $livreur, 403, "Aucun profil livreur n'est lié à ce compte.");

        return $livreur;
    }
}