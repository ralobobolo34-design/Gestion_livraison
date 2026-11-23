<?php

namespace App\Http\Controllers;

use App\Models\Colis;
use App\Models\Commande;
use Illuminate\Http\Request;

class EspaceClientController extends Controller
{
    public function accueil(Request $request)
    {
        $client = $request->user()->client;

        abort_if(! $client, 403, "Aucun profil client n'est lié à ce compte.");

        $commandes = Commande::where('client_id', $client->id);
        $colis     = Colis::whereHas('commande', fn ($q) => $q->where('client_id', $client->id));

        // Reste à régler : montant moins les paiements encaissés ou en attente
        $reste = (clone $commandes)->where('statut', '!=', 'ANNULEE')
            ->withSum(['paiements as engage' => fn ($q) => $q->whereIn('statut', ['PAYE', 'PARTIEL', 'EN_ATTENTE'])], 'montant')
            ->get()
            ->sum(fn ($c) => max(0, $c->montant - (int) $c->engage));

        $stats = [
            'commandes'    => (clone $commandes)->count(),
            'en_cours'     => (clone $commandes)->whereNotIn('statut', ['LIVREE', 'ANNULEE'])->count(),
            'en_livraison' => (clone $colis)->where('statut', 'EN_LIVRAISON')->count(),
            'livres'       => (clone $colis)->where('statut', 'LIVRE')->count(),
            'reste'        => $reste,
        ];

        $colisEnCours = (clone $colis)->whereNotIn('statut', ['LIVRE', 'ANNULE'])
            ->with('commande')->latest('id')->take(8)->get();

        $dernieres = (clone $commandes)->with('zone')->latest('id')->take(5)->get();

        return view('espace_client.accueil', compact('client', 'stats', 'colisEnCours', 'dernieres'));
    }
}