<?php

namespace App\Http\Controllers;

use App\Http\Requests\TourneeRequest;
use App\Models\Colis;
use App\Models\Livreur;
use App\Models\Tournee;
use App\Models\Zone;
use App\Services\TourneeService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TourneeController extends Controller
{
    public function index(Request $request)
    {
        $tournees = Tournee::query()
            ->with('livreur')
            ->withCount('colis')
            ->when($request->q, fn ($query, $q) => $query->where('reference', 'like', "%{$q}%"))
            ->when($request->livreur_id, fn ($query, $l) => $query->where('livreur_id', $l))
            ->when($request->statut, fn ($query, $s) => $query->where('statut', $s))
            ->when($request->zone_id, fn ($query, $z) => $query->whereHas('colis.commande', fn ($c) => $c->where('zone_id', $z)))
            ->when($request->du, fn ($query, $d) => $query->whereDate('date', '>=', $d))
            ->when($request->au, fn ($query, $d) => $query->whereDate('date', '<=', $d))
            ->latest('date')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $livreurs = Livreur::orderBy('nom')->get();
        $zones    = Zone::orderBy('nom')->get();

        return view('tournees.index', compact('tournees', 'livreurs', 'zones'));
    }

    public function create()
    {
        // Seuls les livreurs ACTIF peuvent être choisis (RM02)
        $livreurs = Livreur::where('statut', 'ACTIF')->orderBy('nom')->get();

        return view('tournees.create', compact('livreurs'));
    }

    public function store(TourneeRequest $request, TourneeService $service)
    {
        $tournee = $service->creer($request->validated());

        return redirect()->route('tournees.show', $tournee)
            ->with('success', "Tournée {$tournee->reference} créée. Ajoutez maintenant des colis.");
    }

    public function show(Tournee $tournee)
    {
        $tournee->load(['livreur', 'colis.commande.client', 'colis.commande.zone']);

        // Colis que l'on peut encore affecter (RM03, RM04, RM09)
        $disponibles = $tournee->statut === 'PLANIFIEE'
            ? Colis::with('commande.client')
                ->whereIn('statut', Tournee::COLIS_AFFECTABLES)
                ->whereHas('commande', fn ($q) => $q->where('statut', '!=', 'ANNULEE'))
                ->whereDoesntHave('tournees', fn ($q) => $q->whereIn('tournees.statut', Tournee::ACTIVES))
                ->orderBy('reference')
                ->get()
            : collect();

        return view('tournees.show', compact('tournee', 'disponibles'));
    }

    public function changerStatut(Request $request, Tournee $tournee, TourneeService $service)
    {
        $data = $request->validate([
            'statut' => ['required', Rule::in(array_keys(Tournee::TRANSITIONS))],
        ]);

        $service->changerStatut($tournee, $data['statut'], $request->user()?->id);

        return back()->with('success', 'Statut de la tournée mis à jour.');
    }

    public function ajouterColis(Request $request, Tournee $tournee, TourneeService $service)
    {
        $data = $request->validate(['colis_id' => ['required', 'exists:colis,id']]);

        $service->ajouterColis($tournee, (int) $data['colis_id']);

        return back()->with('success', 'Colis ajouté à la tournée.');
    }

    public function retirerColis(Tournee $tournee, Colis $colis, TourneeService $service)
    {
        $service->retirerColis($tournee, $colis);

        return back()->with('success', 'Colis retiré de la tournée.');
    }
}