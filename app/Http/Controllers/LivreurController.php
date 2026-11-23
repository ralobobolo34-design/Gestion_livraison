<?php

namespace App\Http\Controllers;

use App\Http\Requests\LivreurRequest;
use App\Models\Livreur;
use Illuminate\Http\Request;

class LivreurController extends Controller
{
    public function index(Request $request)
    {
        $livreurs = Livreur::query()
            ->when($request->q, function ($query, $q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('nom', 'like', "%{$q}%")
                        ->orWhere('prenom', 'like', "%{$q}%")
                        ->orWhere('telephone', 'like', "%{$q}%")
                        ->orWhere('numero_vehicule', 'like', "%{$q}%");
                });
            })
            ->when($request->statut, fn ($query, $s) => $query->where('statut', $s))
            ->when($request->vehicule, fn ($query, $v) => $query->where('type_vehicule', $v))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('livreurs.index', compact('livreurs'));
    }

    public function create()
    {
        return view('livreurs.create');
    }

    public function store(LivreurRequest $request)
    {
        Livreur::create($request->validated() + ['date_inscription' => now()]);

        return redirect()->route('livreurs.index')->with('success', 'Livreur ajouté.');
    }

    public function show(Livreur $livreur)
    {
        $tournees = $livreur->tournees()->withCount('colis')->latest('date')->get();

        return view('livreurs.show', compact('livreur', 'tournees'));
    }

    public function edit(Livreur $livreur)
    {
        return view('livreurs.edit', compact('livreur'));
    }

    public function update(LivreurRequest $request, Livreur $livreur)
    {
        $livreur->update($request->validated());

        return redirect()->route('livreurs.index')->with('success', 'Livreur modifié.');
    }

    // Désactivation, pas de suppression
    public function destroy(Livreur $livreur)
    {
        $livreur->update(['statut' => 'INACTIF']);

        return redirect()->route('livreurs.index')->with('success', 'Livreur désactivé.');
    }
}