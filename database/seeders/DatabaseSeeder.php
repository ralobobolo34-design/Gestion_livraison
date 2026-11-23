<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Colis;
use App\Models\Commande;
use App\Models\HistoriqueStatut;
use App\Models\Livreur;
use App\Models\Paiement;
use App\Models\Quartier;
use App\Models\Tarif;
use App\Models\Tournee;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // --- Utilisateurs ---
        $admin = User::create([
            'name' => 'Administrateur', 'email' => 'admin@gestion.mg',
            'password' => Hash::make('password'), 'role' => 'admin',
        ]);
        $agent = User::create([
            'name' => 'Agent Test', 'email' => 'agent@gestion.mg',
            'password' => Hash::make('password'), 'role' => 'agent',
        ]);
        $userLivreur = User::create([
            'name' => 'Livreur Test', 'email' => 'livreur@gestion.mg',
            'password' => Hash::make('password'), 'role' => 'livreur',
        ]);
        $userClient = User::create([
            'name' => 'Client Test', 'email' => 'client@gestion.mg',
            'password' => Hash::make('password'), 'role' => 'client',
        ]);

        // --- Zones, quartiers et tarifs ---
        $tranches = [[0, 1, 3000], [1, 3, 5000], [3, 5, 8000], [5, 10, 12000]];

        $donneesZones = [
            ['Zone A', 3000, ['Analakely', 'Ambohijatovo', 'Behoririka', 'Isoraka', 'Antaninarenina']],
            ['Zone B', 4000, ['Ankorondrano', 'Ivandry', 'Alarobia', 'Ambatobe', 'Andraharo']],
            ['Zone C', 5000, ['Ambohimanarina', 'Ambohipo', 'Anosibe', 'Ankadifotsy', 'Tsiadana']],
            ['Zone D', 6000, ['Ivato', 'Talatamaty', 'Ambohimangakely', 'Sabotsy Namehana']],
        ];

        collect($donneesZones)->each(function ($z, $i) use ($tranches) {
            $zone = Zone::create([
                'nom'         => $z[0],
                'description' => 'Zone de livraison ' . $z[0],
                'tarif_base'  => $z[1],
                'statut'      => 'ACTIF',
            ]);

            foreach ($z[2] as $nom) {
                $zone->quartiers()->create(['nom' => $nom]);
            }

            foreach ($tranches as [$min, $max, $prix]) {
                Tarif::create([
                    'zone_id'        => $zone->id,
                    'poids_min'      => $min,
                    'poids_max'      => $max,
                    'type_livraison' => 'STANDARD',
                    'priorite'       => 'NORMALE',
                    'prix'           => (int) round($prix * (1 + $i * 0.2), -2),
                ]);
            }
        });

        $quartiers = Quartier::all();

        // --- Clients et livreurs ---
        $clientTest = Client::factory()->create([
            'user_id' => $userClient->id, 'nom' => 'Client', 'prenom' => 'Test',
        ]);
        $clients = Client::factory()->count(19)->create()->push($clientTest);

        // Le livreur de test est créé en premier : il reçoit la première tournée
        Livreur::factory()->create([
            'user_id' => $userLivreur->id, 'nom' => 'Livreur', 'prenom' => 'Test', 'statut' => 'ACTIF',
        ]);
        Livreur::factory()->count(7)->create();

        // --- Commandes et colis ---
        $numCmd = 1;
        $numColis = 1;

        for ($i = 0; $i < 30; $i++) {
            // La zone se déduit du quartier de destination
            $quartier = $quartiers->random();

            $commande = Commande::factory()->create([
                'reference'           => sprintf('CMD-2026-%05d', $numCmd++),
                // Les 3 premières commandes appartiennent au client de test
                'client_id'           => $i < 3 ? $clientTest->id : $clients->random()->id,
                'zone_id'             => $quartier->zone_id,
                'quartier_id'         => $quartier->id,
                'adresse_destination' => 'Lot ' . fake()->numberBetween(1, 120) . ', ' . $quartier->nom,
                'user_id'             => $agent->id,
                // Quelques commandes sont forcées « en livraison » : le livreur de test a ainsi toujours des colis
                ...(in_array($i, [0, 1, 3, 4]) ? ['statut' => 'EN_LIVRAISON'] : []),
            ]);

            $statutColis = match ($commande->statut) {
                'LIVREE' => 'LIVRE',
                'ANNULEE' => 'ANNULE',
                'EN_LIVRAISON' => 'EN_LIVRAISON',
                'EN_PREPARATION' => 'ARRIVE_AGENCE',
                default => 'CREE',
            };

            $total = 0;
            foreach (range(1, fake()->numberBetween(1, 3)) as $_) {
                $colis = Colis::factory()->make();
                $tarif = Tarif::where('zone_id', $commande->zone_id)
                    ->where('poids_min', '<', $colis->poids)
                    ->where('poids_max', '>=', $colis->poids)
                    ->first();
                $prix = $tarif?->prix ?? 0;

                $colis = Colis::create([
                    ...$colis->toArray(),
                    'reference' => sprintf('COL-2026-%05d', $numColis++),
                    'commande_id' => $commande->id,
                    'prix' => $prix,
                    'statut' => $statutColis,
                ]);
                $total += $prix;

                // Historique des statuts (RM06), avec des dates étalées dans le temps
                HistoriqueStatut::create([
                    'colis_id'       => $colis->id,
                    'user_id'        => $agent->id,
                    'ancien_statut'  => null,
                    'nouveau_statut' => 'CREE',
                    'created_at'     => $commande->date_commande,
                ]);
                if ($statutColis !== 'CREE') {
                    HistoriqueStatut::create([
                        'colis_id'       => $colis->id,
                        'user_id'        => $agent->id,
                        'ancien_statut'  => 'CREE',
                        'nouveau_statut' => $statutColis,
                        'created_at'     => $this->dateApres($commande, 1, 3),
                    ]);
                }
            }

            $commande->update(['montant' => $total]);

            // --- Paiements (jamais au-dessus du montant, RM07) ---
            if ($commande->statut === 'LIVREE') {
                $this->paiement($commande, $agent, $commande->montant, 'PAYE');
            } elseif ($commande->statut !== 'ANNULEE' && $commande->montant > 0 && fake()->boolean(50)) {
                $partie = (int) floor($commande->montant / 2);
                $this->paiement($commande, $agent, $partie, 'PARTIEL');
            }
        }

        // --- Tournées (livreurs ACTIF uniquement, RM02) ---
        $livreursActifs = Livreur::where('statut', 'ACTIF')->get();
        $colisEnLivraison = Colis::where('statut', 'EN_LIVRAISON')->get()->chunk(3);

        foreach ($colisEnLivraison as $i => $groupe) {
            $tournee = Tournee::create([
                'reference' => sprintf('TRN-2026-%05d', $i + 1),
                'date' => now()->addDays($i),
                'livreur_id' => $livreursActifs[$i % $livreursActifs->count()]->id,
                'heure_depart' => '08:00',
                'heure_fin' => '17:00',
                'statut' => 'PLANIFIEE',
            ]);
            foreach ($groupe->values() as $ordre => $colis) {
                $tournee->colis()->attach($colis->id, ['ordre' => $ordre + 1]);
            }
        }
    }

    /** Date située entre $min et $max jours après la commande, jamais dans le futur. */
    private function dateApres(Commande $commande, int $min, int $max)
    {
        $date = $commande->date_commande->copy()->addDays(rand($min, $max));

        return $date->isFuture() ? now() : $date;
    }

    private function paiement(Commande $commande, User $user, int $montant, string $statut): void
    {
        if ($montant <= 0) {
            return;
        }

        Paiement::create([
            'reference'     => 'PAY-' . strtoupper(fake()->unique()->bothify('????####')),
            'commande_id'   => $commande->id,
            'user_id'       => $user->id,
            'montant'       => $montant,
            'mode'          => fake()->randomElement(['ESPECES', 'MOBILE_MONEY', 'CARTE', 'PAIEMENT_LIVRAISON']),
            'date_paiement' => $this->dateApres($commande, 0, 4),
            'statut'        => $statut,
        ]);
    }
}