@extends('layouts.app')

@section('titre', 'Tournées')

@section('contenu')
<div class="rounded-xl bg-white shadow-sm ring-1 ring-slate-200">

    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 p-5">
        <h2 class="text-base font-semibold text-slate-800">Tournées de livraison</h2>
        <a href="{{ route('tournees.create') }}"
           class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">+ Nouvelle tournée</a>
    </div>

    <form method="GET" class="grid gap-3 border-b border-slate-200 p-5 sm:grid-cols-2 lg:grid-cols-4">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Référence..."
               class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
        <select name="livreur_id" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <option value="">Tous les livreurs</option>
            @foreach ($livreurs as $livreur)
                <option value="{{ $livreur->id }}" @selected(request('livreur_id') == $livreur->id)>{{ $livreur->nom }} {{ $livreur->prenom }}</option>
            @endforeach
        </select>
        <select name="zone_id" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <option value="">Toutes les zones</option>
            @foreach ($zones as $zone)
                <option value="{{ $zone->id }}" @selected(request('zone_id') == $zone->id)>{{ $zone->nom }}</option>
            @endforeach
        </select>
        <select name="statut" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <option value="">Tous les statuts</option>
            @foreach (array_keys(\App\Models\Tournee::TRANSITIONS) as $s)
                <option value="{{ $s }}" @selected(request('statut') === $s)>{{ $s }}</option>
            @endforeach
        </select>
        <input type="date" name="du" value="{{ request('du') }}" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
        <input type="date" name="au" value="{{ request('au') }}" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
        <div class="flex gap-3">
            <button class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">Filtrer</button>
            @if (request()->hasAny(['q', 'livreur_id', 'zone_id', 'statut', 'du', 'au']))
                <a href="{{ route('tournees.index') }}" class="px-2 py-2 text-sm text-slate-500 hover:underline">Réinitialiser</a>
            @endif
        </div>
    </form>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-5 py-3">Référence</th>
                    <th class="px-5 py-3">Date</th>
                    <th class="px-5 py-3">Livreur</th>
                    <th class="px-5 py-3">Horaires</th>
                    <th class="px-5 py-3">Colis</th>
                    <th class="px-5 py-3">Statut</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse ($tournees as $tournee)
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-3 font-medium text-slate-800">
                            <a href="{{ route('tournees.show', $tournee) }}" class="hover:underline">{{ $tournee->reference }}</a>
                        </td>
                        <td class="px-5 py-3">{{ $tournee->date->format('d/m/Y') }}</td>
                        <td class="px-5 py-3">{{ $tournee->livreur->nom }} {{ $tournee->livreur->prenom }}</td>
                        <td class="px-5 py-3">{{ substr($tournee->heure_depart, 0, 5) }} - {{ substr($tournee->heure_fin ?? '', 0, 5) }}</td>
                        <td class="px-5 py-3">{{ $tournee->colis_count }}</td>
                        <td class="px-5 py-3"><x-badge :statut="$tournee->statut" /></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-8 text-center text-slate-500">Aucune tournée trouvée.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="border-t border-slate-200 p-4">{{ $tournees->links() }}</div>
</div>
@endsection