<?php

namespace App\Http\Controllers;

use App\Models\Colis;
use App\Services\ColisStatutService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class ColisController extends Controller
{
    public function index(Request $request)
    {
        $colis = Colis::query()
            ->pourUtilisateur($request->user())   // un client ne voit que ses colis
            ->with('commande.client')
            ->when($request->q, function ($query, $q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('reference', 'like', "%{$q}%")
                        ->orWhereHas('commande.client', fn ($c) => $c
                            ->where('nom', 'like', "%{$q}%")
                            ->orWhere('prenom', 'like', "%{$q}%")
                            ->orWhere('telephone', 'like', "%{$q}%"));
                });
            })
            ->when($request->statut, fn ($query, $s) => $query->where('statut', $s))
            ->when($request->du, fn ($query, $d) => $query->whereDate('created_at', '>=', $d))
            ->when($request->au, fn ($query, $d) => $query->whereDate('created_at', '<=', $d))
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('colis.index', compact('colis'));
    }

    public function show(Colis $colis)
    {
        // La policy refuse l'accès à un colis qui n'appartient pas au client connecté
        Gate::authorize('view', $colis);

        $colis->load(['commande.client', 'commande.zone', 'historiques.user', 'tournees.livreur']);

        return view('colis.show', compact('colis'));
    }

    // Réservé à l'équipe par les routes (role:admin,agent)
    public function changerStatut(Request $request, Colis $colis, ColisStatutService $service)
    {
        $data = $request->validate([
            'statut' => ['required', Rule::in(array_keys(ColisStatutService::TRANSITIONS))],
        ]);

        $service->changer($colis, $data['statut'], $request->user()?->id);

        return back()->with('success', 'Statut du colis mis à jour.');
    }
}