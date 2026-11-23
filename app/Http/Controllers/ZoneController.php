<?php

namespace App\Http\Controllers;

use App\Http\Requests\ZoneRequest;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ZoneController extends Controller
{
    public function index(Request $request)
    {
        $zones = Zone::query()
            ->withCount(['tarifs', 'commandes', 'quartiers'])
            ->when($request->q, fn ($query, $q) => $query->where('nom', 'like', "%{$q}%"))
            ->when($request->statut, fn ($query, $s) => $query->where('statut', $s))
            ->orderBy('nom')
            ->paginate(10)
            ->withQueryString();

        return view('zones.index', compact('zones'));
    }

    public function create()
    {
        return view('zones.create');
    }

    public function store(ZoneRequest $request)
    {
        DB::transaction(function () use ($request) {
            $zone = Zone::create($request->safe()->except('quartiers'));
            $zone->synchroniserQuartiers($request->quartiersListe());
        });

        return redirect()->route('zones.index')->with('success', 'Zone ajoutée.');
    }

    public function show(Zone $zone)
    {
        $zone->load(['quartiers' => fn ($q) => $q->orderBy('nom')]);

        $tarifs = $zone->tarifs()->orderBy('type_livraison')->orderBy('priorite')->orderBy('poids_min')->get();

        return view('zones.show', compact('zone', 'tarifs'));
    }

    public function edit(Zone $zone)
    {
        $zone->load(['quartiers' => fn ($q) => $q->orderBy('nom')]);

        return view('zones.edit', compact('zone'));
    }

    public function update(ZoneRequest $request, Zone $zone)
    {
        DB::transaction(function () use ($request, $zone) {
            $zone->update($request->safe()->except('quartiers'));
            $zone->synchroniserQuartiers($request->quartiersListe());
        });

        return redirect()->route('zones.index')->with('success', 'Zone modifiée.');
    }

    // Les commandes référencent les zones : on désactive au lieu de supprimer
    public function destroy(Zone $zone)
    {
        $zone->update(['statut' => 'INACTIF']);

        return redirect()->route('zones.index')->with('success', 'Zone désactivée.');
    }
    // Rappel des zones et quartiers (bouton de la barre du haut)
public function rappel()
{
    $zones = Zone::where('statut', 'ACTIF')
        ->with(['quartiers' => fn ($q) => $q->orderBy('nom')])
        ->orderBy('nom')
        ->get();

    return view('zones._rappel', compact('zones'));
}
}