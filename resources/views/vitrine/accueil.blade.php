@extends('layouts.public')

@section('titre', config('vitrine.nom') . ' · Livraison de colis')

@section('contenu')
@php
    // On récupère l'utilisateur connecté de manière sécurisée (null si visiteur)
    $currentUser = auth()->user();

    // Le bouton principal s'adapte à l'utilisateur
    $estClient = $currentUser?->isClient();
    $ctaUrl    = ($currentUser && ! $estClient) ? $currentUser->homeUrl() : route('commandes.create');
    $ctaTexte  = ($currentUser && ! $estClient) ? 'Accéder à mon espace' : 'Passer une commande';

    $services = [
        [
            'icone'    => 'cube',
            'titre'    => 'Pour les clients',
            'accroche' => 'Envoi rapide et suivi en temps réel',
            'points'   => ['Commande en ligne en quelques minutes', 'Prix calculé automatiquement, sans surprise', 'Suivi de chaque colis, étape par étape'],
            'lien'     => $ctaUrl,
            'cta'      => $ctaTexte,
        ],
        [
            'icone'    => 'truck',
            'titre'    => 'Pour les livreurs',
            'accroche' => "Rejoignez l'équipe, devenez partenaire",
            'points'   => ['Tournées planifiées à l\'avance', 'Application simple : preuve de livraison, signature, photo', 'Paiement régulier et transparent'],
            'lien'     => 'mailto:' . config('vitrine.email') . '?subject=' . rawurlencode('Candidature livreur partenaire'),
            'cta'      => 'Devenir partenaire',
        ],
        [
            'icone'    => 'users',
            'titre'    => 'Pour les entreprises',
            'accroche' => 'Solutions logistiques sur mesure',
            'points'   => ['Livraisons régulières ou à fort volume', 'Tarifs adaptés à vos besoins', 'Interlocuteur dédié et suivi des envois'],
            'lien'     => 'mailto:' . config('vitrine.email') . '?subject=' . rawurlencode('Demande de devis entreprise'),
            'cta'      => 'Demander un devis',
        ],
    ];
@endphp

{{-- Bandeau principal --}}
<section id="accueil" class="mx-auto max-w-6xl px-4 pb-12 pt-16 text-center sm:px-6 sm:pt-24">
    <p class="mx-auto mb-6 inline-flex items-center gap-2 rounded-full border border-slate-300 px-4 py-1.5 text-sm text-slate-600">
        <span class="h-2 w-2 rounded-full bg-green-500"></span> {{ config('vitrine.couverture') }}
    </p>

    <h1 class="mx-auto max-w-3xl text-4xl font-medium tracking-tight sm:text-6xl">
        <span class="bg-gradient-to-r from-blue-500 via-violet-500 to-fuchsia-400 bg-clip-text text-transparent">
            Vos colis livrés rapidement et en toute sécurité
        </span>
    </h1>

    <p class="mx-auto mt-5 max-w-2xl text-lg text-slate-500">
        Expédiez en quelques clics et suivez votre colis à chaque étape, de la prise en charge jusqu'à la remise en main propre.
    </p>

    {{-- Suivi rapide, sans connexion --}}
    <form id="suivi" action="{{ route('suivi') }}" method="GET" class="mx-auto mt-10 max-w-2xl">
        <div class="flex items-center gap-3 rounded-full border border-slate-300 bg-white py-2 pl-5 pr-2 shadow-sm focus-within:border-blue-500">
            <svg class="h-5 w-5 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
            </svg>
            <input type="search" name="reference" required maxlength="30" autocomplete="off"
                   placeholder="Entrez votre numéro de suivi (ex. COL-2026-00001)"
                   aria-label="Numéro de suivi du colis"
                   class="w-full bg-transparent text-sm placeholder:text-slate-400 focus:outline-none">
            <button type="submit" class="shrink-0 rounded-full bg-blue-600 px-5 py-2 text-sm font-medium text-white transition-colors hover:bg-blue-700">
                Suivre
            </button>
        </div>
        <p class="mt-2 text-xs text-slate-400">Aucun compte nécessaire. Le numéro figure sur votre commande.</p>
    </form>

    <div class="mt-8 flex flex-wrap justify-center gap-3">
        <a href="{{ $ctaUrl }}" class="rounded-full bg-blue-600 px-7 py-3 text-sm font-medium text-white transition-colors hover:bg-blue-700">
            {{ $ctaTexte }}
        </a>
        @guest
            <a href="{{ route('register') }}" class="rounded-full border border-slate-300 px-7 py-3 text-sm font-medium text-slate-700 transition-colors hover:bg-slate-100">
                Créer un compte
            </a>
        @endguest
    </div>

    <dl class="mx-auto mt-14 grid max-w-2xl grid-cols-3 gap-4">
        @foreach ([['Colis livrés', $chiffres['livraisons']], ['Clients', $chiffres['clients']], ['Quartiers desservis', $chiffres['quartiers']]] as [$libelle, $valeur])
            <div>
                <dd class="text-3xl font-semibold text-slate-800">{{ number_format($valeur, 0, ',', ' ') }}</dd>
                <dt class="mt-1 text-sm text-slate-500">{{ $libelle }}</dt>
            </div>
        @endforeach
    </dl>
</section>

{{-- Services --}}
<section id="services" class="mx-auto max-w-6xl scroll-mt-20 px-4 py-16 sm:px-6">
    <div class="mb-10 text-center">
        <h2 class="text-3xl font-medium tracking-tight text-slate-800">Nos services</h2>
        <p class="mt-2 text-slate-500">Une solution adaptée à chaque besoin.</p>
    </div>

    <div class="grid gap-6 md:grid-cols-3">
        @foreach ($services as $service)
            <div class="flex flex-col rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-100 text-blue-700">
                    <x-icon :name="$service['icone']" class="h-6 w-6" />
                </div>
                <h3 class="text-lg font-semibold text-slate-800">{{ $service['titre'] }}</h3>
                <p class="mt-1 text-sm font-medium text-blue-600">{{ $service['accroche'] }}</p>

                <ul class="mt-4 flex-1 space-y-2 text-sm text-slate-500">
                    @foreach ($service['points'] as $point)
                        <li class="flex gap-2">
                            <span class="text-green-600 font-bold">✓</span>
                            <span>{{ $point }}</span>
                        </li>
                    @endforeach
                </ul>

                <a href="{{ $service['lien'] }}"
                   class="mt-6 rounded-full border border-slate-300 px-5 py-2.5 text-center text-sm font-medium text-slate-700 transition-colors hover:bg-slate-100">
                    {{ $service['cta'] }}
                </a>
            </div>
        @endforeach
    </div>
</section>

{{-- Tarifs --}}
<section id="tarifs" class="mx-auto max-w-6xl scroll-mt-20 px-4 py-16 sm:px-6">
    <div class="mb-10 text-center">
        <h2 class="text-3xl font-medium tracking-tight text-slate-800">Nos tarifs</h2>
        <p class="mt-2 text-slate-500">Le prix dépend de la zone de livraison et du poids du colis.</p>
    </div>

    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
        @forelse ($zones as $zone)
            <div class="flex flex-col justify-between rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                <div>
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-slate-800">{{ $zone->nom }}</h3>
                        <span class="text-xs text-slate-400">{{ $zone->quartiers->count() }} quartiers</span>
                    </div>

                    @if ($zone->tarifs->isNotEmpty())
                        <p class="mt-1 text-sm text-slate-500">
                            À partir de
                            <span class="font-semibold text-slate-800">{{ number_format($zone->tarifs->min('prix'), 0, ',', ' ') }} Ar</span>
                        </p>

                        <dl class="mt-4 space-y-2 text-sm">
                            @foreach ($zone->tarifs as $tarif)
                                <div class="flex justify-between border-b border-slate-100 pb-2">
                                    <dt class="text-slate-500">{{ $tarif->poids_min + 0 }} à {{ $tarif->poids_max + 0 }} kg</dt>
                                    <dd class="font-medium text-slate-800">{{ number_format($tarif->prix, 0, ',', ' ') }} Ar</dd>
                                </div>
                            @endforeach
                        </dl>
                    @endif
                </div>

                <div class="mt-4 flex flex-wrap gap-1.5 pt-2">
                    @foreach ($zone->quartiers as $quartier)
                        <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs text-slate-600">{{ $quartier->nom }}</span>
                    @endforeach
                </div>
            </div>
        @empty
            <p class="text-center text-slate-500 sm:col-span-2 lg:col-span-4">Les tarifs seront bientôt disponibles.</p>
        @endforelse
    </div>

    <p class="mt-6 text-center text-sm text-slate-500">
        Livraison <strong>EXPRESS</strong> : +{{ $supplementExpress }} % · Priorité <strong>URGENTE</strong> : +{{ $supplementUrgente }} %.
        Le prix exact est calculé automatiquement au moment de la commande.
    </p>
</section>
@endsection