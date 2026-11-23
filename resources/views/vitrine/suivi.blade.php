@extends('layouts.public')

@section('titre', 'Suivi de colis · ' . config('vitrine.nom'))

@section('contenu')
<section class="mx-auto max-w-3xl px-4 py-14 sm:px-6">
    <div class="mb-8 text-center">
        <h1 class="text-3xl font-medium tracking-tight text-slate-800">Suivi de colis</h1>
        <p class="mt-2 text-slate-500">Entrez la référence de votre colis (COL-...) ou de votre commande (CMD-...).</p>
    </div>

    <form action="{{ route('suivi') }}" method="GET">
        <div class="flex items-center gap-3 rounded-full border border-slate-300 bg-white py-2 pl-5 pr-2 shadow-sm focus-within:border-blue-500">
            <input type="search" name="reference" value="{{ $reference }}" required maxlength="30" autocomplete="off" autofocus
                   placeholder="COL-2026-00001"
                   class="w-full bg-transparent text-sm placeholder:text-slate-400 focus:outline-none">
            <button class="shrink-0 rounded-full bg-blue-600 px-5 py-2 text-sm font-medium text-white hover:bg-blue-700">Suivre</button>
        </div>
    </form>

    @if ($erreur)
        <p class="mt-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ $erreur }}</p>
    @endif

    {{-- Résultat : un colis --}}
    @if ($resultat && $resultat['type'] === 'colis')
        @php
            $colis     = $resultat['colis'];
            $etapes    = \App\Services\ColisStatutService::ETAPES;
            $libelles  = \App\Services\ColisStatutService::LIBELLES;
            $statut    = $colis->statut;
            $position  = array_search($statut === 'ECHEC_LIVRAISON' ? 'EN_LIVRAISON' : $statut, $etapes, true);
        @endphp

        <div class="mt-8 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div>
                    <p class="text-sm text-slate-500">Colis</p>
                    <p class="text-lg font-semibold text-slate-800">{{ $colis->reference }}</p>
                </div>
                <p class="text-xl font-semibold {{ $statut === 'LIVRE' ? 'text-green-600' : (in_array($statut, ['ANNULE', 'ECHEC_LIVRAISON']) ? 'text-red-600' : 'text-blue-600') }}">
                    {{ $libelles[$statut] ?? $statut }}
                </p>
            </div>

            @if ($statut === 'ANNULE')
                <p class="mt-5 rounded-xl bg-red-50 px-4 py-3 text-sm text-red-800">Ce colis a été annulé.</p>
            @else
                @if ($statut === 'ECHEC_LIVRAISON')
                    <p class="mt-5 rounded-xl bg-red-50 px-4 py-3 text-sm text-red-800">
                        La livraison n'a pas pu aboutir. Une nouvelle tentative sera organisée, notre équipe peut vous contacter.
                    </p>
                @endif

                <ol class="mt-6 grid grid-cols-2 gap-5 sm:grid-cols-6">
                    @foreach ($etapes as $i => $etape)
                        @php
                            $atteinte = $position !== false && $i <= $position;
                            $courante = $i === $position;
                            $terminee = $atteinte && ($i < $position || $statut === 'LIVRE');
                        @endphp
                        <li class="flex flex-col items-center text-center">
                            <span class="flex h-9 w-9 items-center justify-center rounded-full text-sm font-semibold
                                         {{ $atteinte ? 'bg-blue-600 text-white' : 'bg-slate-200 text-slate-500' }}
                                         {{ $courante && $statut !== 'LIVRE' ? 'ring-4 ring-blue-500/30' : '' }}">
                                {{ $terminee ? '✓' : $i + 1 }}
                            </span>
                            <span class="mt-2 text-xs {{ $atteinte ? 'font-medium text-slate-800' : 'text-slate-500' }}">{{ $libelles[$etape] }}</span>
                        </li>
                    @endforeach
                </ol>
            @endif

            @if ($colis->commande->date_livraison_prevue && $statut !== 'LIVRE' && $statut !== 'ANNULE')
                <p class="mt-6 text-sm text-slate-500">
                    Livraison souhaitée : <span class="font-medium text-slate-800">{{ $colis->commande->date_livraison_prevue->format('d/m/Y') }}</span>
                </p>
            @endif

            <h2 class="mb-3 mt-8 text-sm font-semibold text-slate-800">Historique</h2>
            <ul class="space-y-3">
                @foreach ($colis->historiques->sortByDesc('id') as $h)
                    <li class="flex items-start gap-3 text-sm">
                        <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-blue-500"></span>
                        <div>
                            <p class="font-medium text-slate-800">{{ $libelles[$h->nouveau_statut] ?? $h->nouveau_statut }}</p>
                            <p class="text-xs text-slate-500">{{ $h->created_at->format('d/m/Y à H:i') }}</p>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Résultat : une commande --}}
    @if ($resultat && $resultat['type'] === 'commande')
        @php
            $commande = $resultat['commande'];
            $libCommande = \App\Models\Commande::LIBELLES;
            $libColis = \App\Services\ColisStatutService::LIBELLES;
        @endphp

        <div class="mt-8 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div>
                    <p class="text-sm text-slate-500">Commande</p>
                    <p class="text-lg font-semibold text-slate-800">{{ $commande->reference }}</p>
                </div>
                <p class="text-xl font-semibold {{ $commande->statut === 'LIVREE' ? 'text-green-600' : ($commande->statut === 'ANNULEE' ? 'text-red-600' : 'text-blue-600') }}">
                    {{ $libCommande[$commande->statut] ?? $commande->statut }}
                </p>
            </div>

            <p class="mt-3 text-sm text-slate-500">Passée le {{ $commande->date_commande->format('d/m/Y') }}</p>

            <h2 class="mb-3 mt-6 text-sm font-semibold text-slate-800">Colis ({{ $commande->colis->count() }})</h2>
            <ul class="divide-y divide-slate-200">
                @foreach ($commande->colis as $colis)
                    <li class="flex items-center justify-between py-3 text-sm">
                        <a href="{{ route('suivi', ['reference' => $colis->reference]) }}" class="font-medium text-blue-600 hover:underline">{{ $colis->reference }}</a>
                        <span class="text-slate-500">{{ $libColis[$colis->statut] ?? $colis->statut }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</section>
@endsection