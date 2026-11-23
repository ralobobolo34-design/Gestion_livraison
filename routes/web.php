<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ColisController;
use App\Http\Controllers\CommandeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EspaceClientController;
use App\Http\Controllers\EspaceLivreurController;
use App\Http\Controllers\LivreurController;
use App\Http\Controllers\PaiementController;
use App\Http\Controllers\TarifController;
use App\Http\Controllers\TourneeController;
use App\Http\Controllers\VitrineController;
use App\Http\Controllers\ZoneController;
use Illuminate\Support\Facades\Route;

// --- Site public (vitrine) ---
Route::get('/', [VitrineController::class, 'accueil'])->name('accueil');
Route::get('/suivi', [VitrineController::class, 'suivi'])->middleware('throttle:20,1')->name('suivi');

// --- Connexion et inscription ---
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('login.attempt');

    Route::get('/inscription', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/inscription', [AuthController::class, 'register'])->middleware('throttle:5,1')->name('register.attempt');
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// --- Espace connecté ---
Route::middleware('auth')->group(function () {

    // Administrateur + agent : traitement et gestion (ils ne créent pas de commande)
    Route::middleware('role:admin,agent')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('zones/rappel', [ZoneController::class, 'rappel'])->name('zones.rappel');

        Route::resource('clients', ClientController::class);

        Route::resource('commandes', CommandeController::class)->only(['edit', 'update']);
        Route::patch('commandes/{commande}/statut', [CommandeController::class, 'changerStatut'])->name('commandes.statut');

        Route::patch('colis/{colis}/statut', [ColisController::class, 'changerStatut'])->name('colis.statut');

        Route::resource('paiements', PaiementController::class)->only(['create', 'store']);
        Route::patch('paiements/{paiement}/confirmer', [PaiementController::class, 'confirmer'])->name('paiements.confirmer');
        Route::patch('paiements/{paiement}/annuler', [PaiementController::class, 'annuler'])->name('paiements.annuler');

        Route::resource('tournees', TourneeController::class)
            ->only(['index', 'create', 'store', 'show'])
            ->parameters(['tournees' => 'tournee']);
        Route::patch('tournees/{tournee}/statut', [TourneeController::class, 'changerStatut'])->name('tournees.statut');
        Route::post('tournees/{tournee}/colis', [TourneeController::class, 'ajouterColis'])->name('tournees.colis.ajouter');
        Route::delete('tournees/{tournee}/colis/{colis}', [TourneeController::class, 'retirerColis'])->name('tournees.colis.retirer');
    });

    // Administrateur seulement
    Route::middleware('role:admin')->group(function () {
        Route::resource('livreurs', LivreurController::class);
        Route::resource('zones', ZoneController::class);
        Route::resource('tarifs', TarifController::class);
    });

    // Livreur
    Route::middleware('role:livreur')->prefix('livreur')->name('livreur.')->group(function () {
        Route::get('tournees', [EspaceLivreurController::class, 'index'])->name('tournees');
        Route::get('tournees/{tournee}', [EspaceLivreurController::class, 'show'])->name('tournees.show');
        Route::patch('tournees/{tournee}/statut', [EspaceLivreurController::class, 'changerStatut'])->name('tournees.statut');
        Route::post('colis/{colis}/livrer', [EspaceLivreurController::class, 'livrer'])->name('colis.livrer');
        Route::post('colis/{colis}/echec', [EspaceLivreurController::class, 'echec'])->name('colis.echec');
    });

    // Client : son espace, ses commandes et ses paiements
    Route::middleware('role:client')->group(function () {
        Route::get('mon-espace', [EspaceClientController::class, 'accueil'])->name('espace.client');

        Route::get('commandes/create', [CommandeController::class, 'create'])->name('commandes.create');
        Route::post('commandes', [CommandeController::class, 'store'])->name('commandes.store');
        Route::post('commandes/estimation', [CommandeController::class, 'estimer'])->name('commandes.estimation');
        Route::patch('commandes/{commande}/annuler', [CommandeController::class, 'annuler'])->name('commandes.annuler');

        Route::get('commandes/{commande}/payer', [PaiementController::class, 'payer'])->name('paiements.payer');
        Route::post('commandes/{commande}/payer', [PaiementController::class, 'declarer'])->name('paiements.declarer');
        Route::patch('paiements/{paiement}/retirer', [PaiementController::class, 'retirer'])->name('paiements.retirer');
    });

    // Staff + client en lecture (le client est limité à ses données par les scopes et les policies)
    // Les livreurs n'y ont pas accès : ils passent par leur espace
    Route::middleware('role:admin,agent,client')->group(function () {
        Route::resource('commandes', CommandeController::class)->only(['index', 'show']);
        Route::resource('colis', ColisController::class)->only(['index', 'show'])->parameters(['colis' => 'colis']);
        Route::resource('paiements', PaiementController::class)->only(['index']);
    });
});

// Toujours en dernier
Route::fallback(function () {
    return 'please try another route';
});