<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Colis;
use App\Models\Commande;
use App\Models\Zone;
use App\Services\TarificationService;
use Illuminate\Http\Request;

class VitrineController extends Controller
{
    public function accueil()
    {
        // Les tarifs publics sont ceux de la base : zones actives, tarif standard et normal
        $zones = Zone::where('statut', 'ACTIF')
            ->with([
                'quartiers' => fn ($q) => $q->orderBy('nom'),
                'tarifs'    => fn ($q) => $q->where('type_livraison', 'STANDARD')
                                            ->where('priorite', 'NORMALE')
                                            ->orderBy('poids_min'),
            ])
            ->orderBy('nom')
            ->get();

        $chiffres = [
            'livraisons' => Colis::where('statut', 'LIVRE')->count(),
            'clients'    => Client::where('statut', 'ACTIF')->count(),
            'quartiers'  => $zones->sum(fn ($z) => $z->quartiers->count()),
        ];

        $supplementExpress = (int) round(TarificationService::SUPPLEMENT_EXPRESS * 100);
        $supplementUrgente = (int) round(TarificationService::SUPPLEMENT_URGENTE * 100);

        return view('vitrine.accueil', compact('zones', 'chiffres', 'supplementExpress', 'supplementUrgente'));
    }

    // Suivi public : ne montre que l'avancement, jamais de nom, d'adresse ni de prix
    public function suivi(Request $request)
    {
        $reference = mb_substr(strtoupper(trim((string) $request->query('reference'))), 0, 30);
        $resultat = null;
        $erreur = null;

        if ($reference !== '') {
            if (str_starts_with($reference, 'COL-')) {
                $colis = Colis::with(['commande', 'historiques'])->where('reference', $reference)->first();
                $resultat = $colis ? ['type' => 'colis', 'colis' => $colis] : null;
            } elseif (str_starts_with($reference, 'CMD-')) {
                $commande = Commande::with('colis')->where('reference', $reference)->first();
                $resultat = $commande ? ['type' => 'commande', 'commande' => $commande] : null;
            }

            if (! $resultat) {
                $erreur = 'Aucun colis ni commande ne correspond à cette référence. Vérifiez-la (exemple : COL-2026-00001 ou CMD-2026-00001).';
            }
        }

        return view('vitrine.suivi', compact('reference', 'resultat', 'erreur'));
    }
}