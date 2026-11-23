<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommandeRequest;
use App\Models\Commande;
use App\Models\Quartier;
use App\Models\Zone;
use App\Services\CommandeService;
use App\Services\TarificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class CommandeController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $commandes = Commande::query()
            ->with(['client', 'zone'])
            ->pourUtilisateur($user)
            ->withCount('colis')
            ->when($request->q, function ($query, $q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('reference', 'like', "%{$q}%")
                        ->orWhereHas('client', fn ($c) => $c
                            ->where('nom', 'like', "%{$q}%")
                            ->orWhere('prenom', 'like', "%{$q}%")
                            ->orWhere('telephone', 'like', "%{$q}%"));
                });
            })
            ->when($request->statut, fn ($query, $s) => $query->where('statut', $s))
            ->when($request->du, fn ($query, $d) => $query->whereDate('date_commande', '>=', $d))
            ->when($request->au, fn ($query, $d) => $query->whereDate('date_commande', '<=', $d))
            ->when($request->filled('montant_min'), fn ($query) => $query->where('montant', '>=', $request->montant_min))
            ->when($request->filled('montant_max'), fn ($query) => $query->where('montant', '<=', $request->montant_max))
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('commandes.index', compact('commandes'));
    }

    // Réservé au client (route role:client)
    public function create(Request $request)
    {
        $client = $request->user()->client;

        abort_if(! $client, 403, "Aucun profil client n'est lié à ce compte.");

        $zones = Zone::where('statut', 'ACTIF')
            ->has('quartiers')
            ->with(['quartiers' => fn ($q) => $q->orderBy('nom')])
            ->orderBy('nom')
            ->get();

        return view('commandes.create', compact('zones', 'client'));
    }

    public function store(CommandeRequest $request, CommandeService $service)
    {
        $commande = $service->creer($request->validated(), $request->user()->id);

        return redirect()->route('commandes.show', $commande)
            ->with('success', "Votre commande {$commande->reference} est enregistrée. Notre équipe va la confirmer.");
    }

    // Prix estimé en direct dans le formulaire (même calcul que l'enregistrement)
    public function estimer(Request $request, TarificationService $tarifs)
    {
        $data = $request->validate([
            'quartier_id'    => ['required', 'exists:quartiers,id'],
            'type_livraison' => ['required', Rule::in(['STANDARD', 'EXPRESS'])],
            'priorite'       => ['required', Rule::in(['NORMALE', 'URGENTE'])],
            'colis'          => ['required', 'array', 'max:10'],
            'colis.*.poids'  => ['nullable', 'numeric', 'min:0.01', 'max:10'],
        ]);

        $zone = Quartier::with('zone')->findOrFail($data['quartier_id'])->zone;

        $lignes = [];
        $total = 0;

        foreach (array_values($data['colis']) as $ligne) {
            $poids = (float) ($ligne['poids'] ?? 0);

            if ($poids <= 0) {
                $lignes[] = ['statut' => 'vide'];
                continue;
            }

            $prix = $tarifs->prixColis($zone, $poids, $data['type_livraison'], $data['priorite']);

            if ($prix === null) {
                $lignes[] = ['statut' => 'hors_grille'];
                continue;
            }

            $lignes[] = ['statut' => 'ok', 'prix' => $prix];
            $total += $prix;
        }

        return response()->json(['zone' => $zone->nom, 'lignes' => $lignes, 'total' => $total]);
    }

    public function show(Commande $commande)
    {
        Gate::authorize('view', $commande);

        $commande->load(['client', 'zone', 'quartier', 'colis', 'paiements']);

        return view('commandes.show', compact('commande'));
    }

    // --- Traitement par l'équipe (admin et agent) ---

    public function edit(Commande $commande)
    {
        if (! $this->modifiable($commande)) {
            return redirect()->route('commandes.show', $commande)
                ->with('error', 'Cette commande ne peut plus être modifiée.');
        }

        return view('commandes.edit', compact('commande'));
    }

    // Seules les adresses et la date prévue sont modifiables : changer la zone ou
    // le type obligerait à recalculer tous les prix.
    public function update(Request $request, Commande $commande)
    {
        if (! $this->modifiable($commande)) {
            return redirect()->route('commandes.show', $commande)
                ->with('error', 'Cette commande ne peut plus être modifiée.');
        }

        $data = $request->validate([
            'adresse_depart'        => ['required', 'string', 'max:255'],
            'adresse_destination'   => ['required', 'string', 'max:255'],
            'date_livraison_prevue' => ['nullable', 'date', 'after_or_equal:' . $commande->date_commande->toDateString()],
        ]);

        $commande->update($data);

        return redirect()->route('commandes.show', $commande)->with('success', 'Commande modifiée.');
    }

    public function changerStatut(Request $request, Commande $commande, CommandeService $service)
    {
        $data = $request->validate([
            'statut' => ['required', Rule::in(array_keys(Commande::TRANSITIONS))],
        ]);

        $service->changerStatut($commande, $data['statut'], $request->user()->id);

        return back()->with('success', 'Statut de la commande mis à jour.');
    }

    // Le client annule sa propre commande, tant qu'elle est en attente
    public function annuler(Request $request, Commande $commande, CommandeService $service)
    {
        Gate::authorize('annuler', $commande);

        $service->changerStatut($commande, 'ANNULEE', $request->user()->id);

        return redirect()->route('commandes.show', $commande)->with('success', 'Votre commande a été annulée.');
    }

    private function modifiable(Commande $commande): bool
    {
        return in_array($commande->statut, ['EN_ATTENTE', 'CONFIRMEE'], true);
    }
}