<?php

namespace App\Http\Controllers;

use App\Http\Requests\TarifRequest;
use App\Models\Tarif;
use App\Models\Zone;
use Illuminate\Http\Request;

class TarifController extends Controller
{
    public function index(Request $request)
    {
        $tarifs = Tarif::query()
            ->with('zone')
            ->when($request->zone_id, fn ($query, $z) => $query->where('zone_id', $z))
            ->when($request->type_livraison, fn ($query, $t) => $query->where('type_livraison', $t))
            ->orderBy('zone_id')
            ->orderBy('type_livraison')
            ->orderBy('priorite')
            ->orderBy('poids_min')
            ->paginate(15)
            ->withQueryString();

        $zones = Zone::orderBy('nom')->get();

        return view('tarifs.index', compact('tarifs', 'zones'));
    }

    public function create()
    {
        $zones = Zone::where('statut', 'ACTIF')->orderBy('nom')->get();

        return view('tarifs.create', compact('zones'));
    }

    public function store(TarifRequest $request)
    {
        Tarif::create($request->validated());

        return redirect()->route('tarifs.index')->with('success', 'Tarif ajouté.');
    }

    public function show(Tarif $tarif)
    {
        return redirect()->route('tarifs.edit', $tarif);
    }

    public function edit(Tarif $tarif)
    {
        $zones = Zone::orderBy('nom')->get();

        return view('tarifs.edit', compact('tarif', 'zones'));
    }

    public function update(TarifRequest $request, Tarif $tarif)
    {
        $tarif->update($request->validated());

        return redirect()->route('tarifs.index')->with('success', 'Tarif modifié.');
    }

    public function destroy(Tarif $tarif)
    {
        $tarif->delete();

        return redirect()->route('tarifs.index')->with('success', 'Tarif supprimé.');
    }
}