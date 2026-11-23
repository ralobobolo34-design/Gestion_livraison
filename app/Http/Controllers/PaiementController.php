<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaiementClientRequest;
use App\Http\Requests\PaiementRequest;
use App\Models\Commande;
use App\Models\Paiement;
use App\Services\PaiementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PaiementController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $paiements = Paiement::query()
            ->with('commande.client')
            // Un client ne voit que les paiements de ses propres commandes
            ->pourUtilisateur($user)
            ->when($request->q, function ($query, $q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('reference', 'like', "%{$q}%")
                        ->orWhere('reference_transaction', 'like', "%{$q}%")
                        ->orWhereHas('commande', fn ($c) => $c->where('reference', 'like', "%{$q}%"))
                        ->orWhereHas('commande.client', fn ($c) => $c
                            ->where('nom', 'like', "%{$q}%")
                            ->orWhere('prenom', 'like', "%{$q}%"));
                });
            })
            ->when($request->mode, fn ($query, $m) => $query->where('mode', $m))
            ->when($request->statut, fn ($query, $s) => $query->where('statut', $s))
            ->when($request->du, fn ($query, $d) => $query->whereDate('date_paiement', '>=', $d))
            ->when($request->au, fn ($query, $d) => $query->whereDate('date_paiement', '<=', $d))
            ->latest('date_paiement')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        // Pour le client : ses commandes qui ont encore un reste à régler
        $aRegler = $user->isClient()
            ? Commande::where('client_id', $user->client?->id)
                ->where('statut', '!=', 'ANNULEE')
                ->where('montant', '>', 0)
                ->latest('id')
                ->get()
                ->filter(fn (Commande $c) => $c->montantRestant() > 0)
            : collect();

        return view('paiements.index', compact('paiements', 'aRegler'));
    }

    // --- Parcours du client (routes role:client) ---

    public function payer(Commande $commande)
    {
        Gate::authorize('payer', $commande);

        if ($commande->statut === 'ANNULEE' || $commande->montantRestant() <= 0) {
            return redirect()->route('commandes.show', $commande)
                ->with('error', 'Cette commande ne peut plus être payée : elle est déjà réglée, en cours de vérification ou annulée.');
        }

        $commande->load('paiements');

        return view('paiements.payer', ['commande' => $commande, 'restant' => $commande->montantRestant()]);
    }

    public function declarer(PaiementClientRequest $request, Commande $commande, PaiementService $service)
    {
        Gate::authorize('payer', $commande);

        $data = $request->validated();

        // Les détails Mobile Money n'ont de sens que pour ce mode
        if ($data['mode'] !== 'MOBILE_MONEY') {
            $data['operateur'] = null;
            $data['reference_transaction'] = null;
        }

        $paiement = $service->declarer($commande, $data, $request->user()->id);

        $message = $paiement->mode === 'MOBILE_MONEY'
            ? "Paiement {$paiement->reference} enregistré. Notre équipe vérifie votre transaction et le confirme sous peu."
            : "Paiement {$paiement->reference} enregistré : vous réglerez en espèces au livreur à la livraison.";

        return redirect()->route('commandes.show', $commande)->with('success', $message);
    }

    // Le client retire sa propre déclaration, tant qu'elle n'est pas confirmée
    public function retirer(Paiement $paiement, PaiementService $service)
    {
        Gate::authorize('retirer', $paiement);

        $service->annuler($paiement);

        return back()->with('success', 'Votre déclaration de paiement a été retirée.');
    }

    // --- Réservé à l'équipe (routes role:admin,agent) ---

    public function create()
    {
        // Seules les commandes non annulées avec un reste à régler
        $commandes = Commande::with('client')
            ->where('statut', '!=', 'ANNULEE')
            ->where('montant', '>', 0)
            ->latest('id')
            ->get()
            ->filter(fn (Commande $c) => $c->montantRestant() > 0);

        return view('paiements.create', compact('commandes'));
    }

    public function store(PaiementRequest $request, PaiementService $service)
    {
        $paiement = $service->enregistrer($request->validated(), $request->user()?->id);

        return redirect()->route('commandes.show', $paiement->commande_id)
            ->with('success', "Paiement {$paiement->reference} enregistré.");
    }

    public function confirmer(Paiement $paiement, PaiementService $service)
    {
        $service->confirmer($paiement);

        return back()->with('success', 'Paiement confirmé.');
    }

    public function annuler(Paiement $paiement, PaiementService $service)
    {
        $service->annuler($paiement);

        return back()->with('success', 'Paiement annulé.');
    }
}