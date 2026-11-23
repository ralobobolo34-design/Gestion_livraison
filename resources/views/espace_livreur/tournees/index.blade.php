@extends('layouts.app')

@section('titre', 'Mes tournées')

@section('contenu')
@php
    $carte = function ($tournee) {
        return $tournee;
    };
@endphp

<div class="mb-6">
    <h2 class="text-lg font-semibold text-slate-800">Bonjour {{ $livreur->prenom }}</h2>
    <p class="text-sm text-slate-500">{{ $livreur->type_vehicule }} {{ $livreur->numero_vehicule }} · {{ $livreur->statut }}</p>
</div>

<h3 class="mb-3 text-sm font-semibold uppercase tracking-wider text-slate-500">En cours et à venir</h3>

<div class="mb-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
    @forelse ($actives as $tournee)
        <a href="{{ route('livreur.tournees.show', $tournee) }}"
           class="block rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200 transition hover:ring-blue-300">
            <div class="mb-2 flex items-center justify-between">
                <span class="font-semibold text-slate-800">{{ $tournee->reference }}</span>
                <x-badge :statut="$tournee->statut" />
            </div>
            <p class="text-sm text-slate-600">
                {{ $tournee->date->isToday() ? "Aujourd'hui" : $tournee->date->format('d/m/Y') }}
                · {{ substr($tournee->heure_depart, 0, 5) }} - {{ substr($tournee->heure_fin ?? '', 0, 5) }}
            </p>
            <div class="mt-3">
                <div class="mb-1 flex justify-between text-xs text-slate-500">
                    <span>{{ $tournee->colis_livres_count }} / {{ $tournee->colis_count }} colis livrés</span>
                </div>
                <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                    <div class="h-2 rounded-full bg-green-500"
                         style="width: {{ $tournee->colis_count ? round($tournee->colis_livres_count / $tournee->colis_count * 100) : 0 }}%"></div>
                </div>
            </div>
        </a>
    @empty
        <p class="rounded-xl bg-white p-6 text-sm text-slate-500 shadow-sm ring-1 ring-slate-200 sm:col-span-2 lg:col-span-3">
            Aucune tournée en cours ou à venir.
        </p>
    @endforelse
</div>

<h3 class="mb-3 text-sm font-semibold uppercase tracking-wider text-slate-500">Historique</h3>

<div class="rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
    <table class="w-full text-left text-sm">
        <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500">
            <tr>
                <th class="px-5 py-3">Référence</th>
                <th class="px-5 py-3">Date</th>
                <th class="px-5 py-3">Colis livrés</th>
                <th class="px-5 py-3">Statut</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">
            @forelse ($historique as $tournee)
                <tr class="hover:bg-slate-50">
                    <td class="px-5 py-3 font-medium text-slate-800">
                        <a href="{{ route('livreur.tournees.show', $tournee) }}" class="hover:underline">{{ $tournee->reference }}</a>
                    </td>
                    <td class="px-5 py-3">{{ $tournee->date->format('d/m/Y') }}</td>
                    <td class="px-5 py-3">{{ $tournee->colis_livres_count }} / {{ $tournee->colis_count }}</td>
                    <td class="px-5 py-3"><x-badge :statut="$tournee->statut" /></td>
                </tr>
            @empty
                <tr><td colspan="4" class="px-5 py-8 text-center text-slate-500">Aucune tournée passée.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection