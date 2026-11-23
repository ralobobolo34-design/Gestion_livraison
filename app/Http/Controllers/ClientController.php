<?php

namespace App\Http\Controllers;

use App\Http\Requests\ClientRequest;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $clients = Client::query()
            ->when($request->q, function ($query, $q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('nom', 'like', "%{$q}%")
                        ->orWhere('prenom', 'like', "%{$q}%")
                        ->orWhere('telephone', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%");
                });
            })
            ->when($request->statut, fn ($query, $s) => $query->where('statut', $s))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('clients.index', compact('clients'));
    }

    public function create()
    {
        return view('clients.create');
    }

    public function store(ClientRequest $request)
    {
        Client::create($request->validated() + ['statut' => 'ACTIF']);

        return redirect()->route('clients.index')->with('success', 'Client ajouté.');
    }

    public function show(Client $client)
    {
        $commandes = $client->commandes()->with('zone')->latest()->get();

        return view('clients.show', compact('client', 'commandes'));
    }

    public function edit(Client $client)
    {
        return view('clients.edit', compact('client'));
    }

    public function update(ClientRequest $request, Client $client)
    {
        $client->update($request->validated());

        return redirect()->route('clients.index')->with('success', 'Client modifié.');
    }

    // Le cahier des charges demande de désactiver, pas de supprimer
    public function destroy(Client $client)
    {
        $client->update(['statut' => 'INACTIF']);

        return redirect()->route('clients.index')->with('success', 'Client désactivé.');
    }
}