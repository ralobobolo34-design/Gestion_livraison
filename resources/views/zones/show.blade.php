@extends('layouts.app')

@section('titre', $zone->nom)

@section('contenu')
<div class="mb-6 rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
    <dl class="grid gap-4 text-sm sm:grid-cols-3">
        <div><dt class="text-slate-500">Tarif de base</dt><dd class="font-medium">{{ number_format($zone->tarif_base, 0, ',', ' ') }} Ar</dd></div>
        <div><dt class="text-slate-500">Statut</dt><dd class="font-medium">{{ $zone->statut }}</dd></div>
        <div class="sm:col-span-3"><dt class="text-slate-500">Description</dt><dd class="font-medium">{{ $zone->description ?? '-' }}</dd></div>
    </dl>
</div>

<div class="mb-6 rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
    <h2 class="mb-3 text-base font-semibold text-slate-800">Quartiers ({{ $zone->quartiers->count() }})</h2>
    <div class="flex flex-wrap gap-2">
        @forelse ($zone->quartiers->sortBy('nom') as $quartier)
            <span class="rounded-full bg-slate-100 px-3 py-1 text-sm text-slate-700">{{ $quartier->nom }}</span>
        @empty
            <p class="text-sm text-slate-500">Aucun quartier. Modifiez la zone pour en ajouter.</p>
        @endforelse
    </div>
</div>

<div class="rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
    <div class="flex items-center justify-between border-b border-slate-200 p-5">
        <h2 class="text-base font-semibold text-slate-800">Grille tarifaire</h2>
        <a href="{{ route('tarifs.create', ['zone_id' => $zone->id]) }}"
           class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">+ Ajouter un tarif</a>
    </div>
    <table class="w-full text-left text-sm">
        <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500">
            <tr>
                <th class="px-5 py-3">Poids</th>
                <th class="px-5 py-3">Type</th>
                <th class="px-5 py-3">Priorité</th>
                <th class="px-5 py-3 text-right">Prix</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">
            @forelse ($tarifs as $tarif)
                <tr>
                    <td class="px-5 py-3">{{ $tarif->poids_min + 0 }} - {{ $tarif->poids_max + 0 }} kg</td>
                    <td class="px-5 py-3">{{ $tarif->type_livraison }}</td>
                    <td class="px-5 py-3">{{ $tarif->priorite }}</td>
                    <td class="px-5 py-3 text-right font-medium">{{ number_format($tarif->prix, 0, ',', ' ') }} Ar</td>
                </tr>
            @empty
                <tr><td colspan="4" class="px-5 py-8 text-center text-slate-500">Aucun tarif pour cette zone.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection