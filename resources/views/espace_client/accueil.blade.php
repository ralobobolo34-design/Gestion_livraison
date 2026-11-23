@extends('layouts.app')

@section('titre', 'Mon espace')

@section('contenu')
<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h2 class="text-lg font-semibold text-slate-800">Bonjour {{ $client->prenom }}</h2>
        <p class="text-sm text-slate-500">Suivez vos commandes et vos colis.</p>
    </div>
    <a href="{{ route('commandes.create') }}"
       class="rounded-full bg-blue-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-700">+ Passer une commande</a>
</div>

<div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    <a href="{{ route('commandes.index') }}" class="block rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200 transition hover:ring-blue-300">
        <p class="text-sm text-slate-500">Mes commandes</p>
        <p class="mt-1 text-3xl font-bold text-slate-800">{{ $stats['commandes'] }}</p>
        <p class="mt-1 text-xs text-slate-400">{{ $stats['en_cours'] }} en cours</p>
    </a>
    <a href="{{ route('colis.index', ['statut' => 'EN_LIVRAISON']) }}" class="block rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200 transition hover:ring-blue-300">
        <p class="text-sm text-slate-500">Colis en livraison</p>
        <p class="mt-1 text-3xl font-bold text-blue-600">{{ $stats['en_livraison'] }}</p>
    </a>
    <a href="{{ route('colis.index', ['statut' => 'LIVRE']) }}" class="block rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200 transition hover:ring-blue-300">
        <p class="text-sm text-slate-500">Colis livrés</p>
        <p class="mt-1 text-3xl font-bold text-green-600">{{ $stats['livres'] }}</p>
    </a>
    <a href="{{ route('paiements.index') }}" class="block rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200 transition hover:ring-blue-300">
        <p class="text-sm text-slate-500">Reste à régler</p>
        <p class="mt-1 text-3xl font-bold {{ $stats['reste'] > 0 ? 'text-orange-600' : 'text-slate-800' }}">{{ number_format($stats['reste'], 0, ',', ' ') }} Ar</p>
    </a>
</div>

<div class="mb-6 rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
    <h2 class="border-b border-slate-200 p-5 text-base font-semibold text-slate-800">Mes colis en cours</h2>
    <div class="divide-y divide-slate-200">
        @forelse ($colisEnCours as $colis)
            <a href="{{ route('colis.show', $colis) }}" class="flex items-center justify-between px-5 py-3 text-sm hover:bg-slate-50">
                <div>
                    <p class="font-medium text-slate-800">{{ $colis->reference }}</p>
                    <p class="text-xs text-slate-500">{{ $colis->description ?? 'Colis' }} · commande {{ $colis->commande->reference }}</p>
                </div>
                <x-badge :statut="$colis->statut" />
            </a>
        @empty
            <p class="px-5 py-8 text-center text-sm text-slate-500">Aucun colis en cours.</p>
        @endforelse
    </div>
</div>

<div class="rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
    <h2 class="border-b border-slate-200 p-5 text-base font-semibold text-slate-800">Mes dernières commandes</h2>
    <table class="w-full text-left text-sm">
        <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500">
            <tr>
                <th class="px-5 py-3">Référence</th>
                <th class="px-5 py-3">Date</th>
                <th class="px-5 py-3 text-right">Montant</th>
                <th class="px-5 py-3">Statut</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">
            @forelse ($dernieres as $commande)
                <tr class="hover:bg-slate-50">
                    <td class="px-5 py-3 font-medium text-slate-800">
                        <a href="{{ route('commandes.show', $commande) }}" class="hover:underline">{{ $commande->reference }}</a>
                    </td>
                    <td class="px-5 py-3">{{ $commande->date_commande->format('d/m/Y') }}</td>
                    <td class="px-5 py-3 text-right">{{ number_format($commande->montant, 0, ',', ' ') }} Ar</td>
                    <td class="px-5 py-3"><x-badge :statut="$commande->statut" /></td>
                </tr>
            @empty
                <tr><td colspan="4" class="px-5 py-8 text-center text-slate-500">Aucune commande.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection