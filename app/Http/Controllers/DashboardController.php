<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Colis;
use App\Models\Commande;
use App\Models\HistoriqueStatut;
use App\Models\Livreur;
use App\Models\Paiement;
use App\Services\ColisStatutService;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // --- Indicateurs ---
        $stats = [
            'clients'            => Client::count(),
            'livreurs'           => Livreur::count(),
            'livreurs_actifs'    => Livreur::where('statut', 'ACTIF')->count(),
            'commandes'          => Commande::count(),
            'colis'              => Colis::count(),
            'colis_en_livraison' => Colis::where('statut', 'EN_LIVRAISON')->count(),
            'colis_livres'       => Colis::where('statut', 'LIVRE')->count(),
            // En retard = pas livré, pas annulé, et la date prévue de la commande est dépassée
            'colis_en_retard'    => Colis::whereNotIn('statut', ['LIVRE', 'ANNULE'])
                ->whereHas('commande', fn ($q) => $q->whereDate('date_livraison_prevue', '<', today()))
                ->count(),
            'commandes_annulees' => Commande::where('statut', 'ANNULEE')->count(),
            'ventes'             => (int) Commande::where('statut', '!=', 'ANNULEE')->sum('montant'),
            'paiements'          => (int) Paiement::where('statut', '!=', 'ANNULE')->sum('montant'),
            'a_verifier' => Paiement::where('statut', 'EN_ATTENTE')->count(),
        ];

        // --- Livraisons par jour (30 derniers jours) ---
        $jours = collect(range(29, 0))->map(fn ($i) => now()->subDays($i)->toDateString());

        $parJour = HistoriqueStatut::where('nouveau_statut', 'LIVRE')
            ->where('created_at', '>=', now()->subDays(29)->startOfDay())
            ->selectRaw('DATE(created_at) as jour, COUNT(*) as total')
            ->groupBy('jour')
            ->pluck('total', 'jour');

        // --- Revenus par mois (6 derniers mois) ---
        $mois = collect(range(5, 0))->map(fn ($i) => now()->startOfMonth()->subMonths($i));

        $ventesParMois = Commande::where('statut', '!=', 'ANNULEE')
            ->where('date_commande', '>=', $mois->first()->toDateString())
            ->selectRaw("DATE_FORMAT(date_commande, '%Y-%m') as m, SUM(montant) as total")
            ->groupBy('m')
            ->pluck('total', 'm');

        $encaisseParMois = Paiement::where('statut', '!=', 'ANNULE')
            ->where('date_paiement', '>=', $mois->first())
            ->selectRaw("DATE_FORMAT(date_paiement, '%Y-%m') as m, SUM(montant) as total")
            ->groupBy('m')
            ->pluck('total', 'm');

        // --- Colis par statut (dans l'ordre du cycle de vie) ---
        $parStatut = Colis::selectRaw('statut, COUNT(*) as total')->groupBy('statut')->pluck('total', 'statut');
        $statuts = array_keys(ColisStatutService::TRANSITIONS);

        // --- Performance des livreurs ---
        $livreurs = DB::table('livreurs')
            ->join('tournees', 'tournees.livreur_id', '=', 'livreurs.id')
            ->join('tournee_details', 'tournee_details.tournee_id', '=', 'tournees.id')
            ->join('colis', 'colis.id', '=', 'tournee_details.colis_id')
            ->groupBy('livreurs.id', 'livreurs.nom', 'livreurs.prenom')
            ->selectRaw("livreurs.nom, livreurs.prenom,
                         COUNT(DISTINCT colis.id) as affectes,
                         COUNT(DISTINCT CASE WHEN colis.statut = 'LIVRE' THEN colis.id END) as livres")
            ->orderByDesc('affectes')
            ->limit(8)
            ->get();

        $graphiques = [
            'jours' => [
                'labels' => $jours->map(fn ($d) => date('d/m', strtotime($d)))->all(),
                'data'   => $jours->map(fn ($d) => (int) ($parJour[$d] ?? 0))->all(),
            ],
            'mois' => [
                'labels'  => $mois->map(fn ($m) => $m->format('m/Y'))->all(),
                'ventes'  => $mois->map(fn ($m) => (int) ($ventesParMois[$m->format('Y-m')] ?? 0))->all(),
                'encaisse' => $mois->map(fn ($m) => (int) ($encaisseParMois[$m->format('Y-m')] ?? 0))->all(),
            ],
            'statuts' => [
                'labels' => array_map(fn ($s) => str_replace('_', ' ', $s), $statuts),
                'data'   => array_map(fn ($s) => (int) ($parStatut[$s] ?? 0), $statuts),
            ],
            'livreurs' => [
                'labels'   => $livreurs->map(fn ($l) => $l->nom . ' ' . mb_substr($l->prenom, 0, 1) . '.')->all(),
                'affectes' => $livreurs->pluck('affectes')->map(fn ($v) => (int) $v)->all(),
                'livres'   => $livreurs->pluck('livres')->map(fn ($v) => (int) $v)->all(),
            ],
        ];

        $dernieresCommandes = Commande::with('client')->latest('id')->take(5)->get();

        return view('dashboard', compact('stats', 'graphiques', 'dernieresCommandes'));
    }
}