@extends('layouts.app')

@section('titre', 'Zones')

@section('contenu')
<div class="rounded-xl bg-white shadow-sm ring-1 ring-slate-200">

    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 p-5">
        <h2 class="text-base font-semibold text-slate-800">Zones de livraison</h2>
        <a href="{{ route('zones.create') }}"
           class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
            + Ajouter une zone
        </a>
    </div>

    <form method="GET" class="flex flex-wrap gap-3 border-b border-slate-200 p-5">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Nom de la zone..."
               class="w-64 rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
        <select name="statut" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <option value="">Tous les statuts</option>
            <option value="ACTIF" @selected(request('statut') === 'ACTIF')>Actif</option>
            <option value="INACTIF" @selected(request('statut') === 'INACTIF')>Inactif</option>
        </select>
        <button class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">Rechercher</button>
        @if (request()->hasAny(['q', 'statut']))
            <a href="{{ route('zones.index') }}" class="px-2 py-2 text-sm text-slate-500 hover:underline">Réinitialiser</a>
        @endif
    </form>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-5 py-3">Zone</th>
                    <th class="px-5 py-3">Tarif de base</th>
                    <th class="px-5 py-3">Tarifs</th>
                    <th class="px-5 py-3">Commandes</th>
                    <th class="px-5 py-3">Statut</th>
                    <th class="px-5 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse ($zones as $zone)
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-3 font-medium text-slate-800">
                            <a href="{{ route('zones.show', $zone) }}" class="hover:underline">{{ $zone->nom }}</a>
                        </td>
                        <td class="px-5 py-3">{{ number_format($zone->tarif_base, 0, ',', ' ') }} Ar</td>
                        <td class="px-5 py-3">{{ $zone->tarifs_count }}</td>
                        <td class="px-5 py-3">{{ $zone->commandes_count }}</td>
                        <td class="px-5 py-3">
                            <span class="rounded-full px-2.5 py-0.5 text-xs font-medium
                                {{ $zone->statut === 'ACTIF' ? 'bg-green-100 text-green-700' : 'bg-slate-200 text-slate-600' }}">
                                {{ $zone->statut }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-right">
                            <a href="{{ route('zones.edit', $zone) }}" class="rounded-lg px-3 py-1 text-blue-600 hover:bg-blue-50">Modifier</a>
                            @if ($zone->statut === 'ACTIF')
                                <form action="{{ route('zones.destroy', $zone) }}" method="POST" class="inline"
                                      onsubmit="return confirm('Désactiver cette zone ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="rounded-lg px-3 py-1 text-red-600 hover:bg-red-50">Désactiver</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-8 text-center text-slate-500">Aucune zone trouvée.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="border-t border-slate-200 p-4">{{ $zones->links() }}</div>
</div>
@endsection