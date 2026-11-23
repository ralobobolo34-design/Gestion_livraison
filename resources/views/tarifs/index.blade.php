@extends('layouts.app')

@section('titre', 'Tarifs')

@section('contenu')
<div class="rounded-xl bg-white shadow-sm ring-1 ring-slate-200">

    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 p-5">
        <h2 class="text-base font-semibold text-slate-800">Grille des tarifs</h2>
        <a href="{{ route('tarifs.create') }}"
           class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">+ Ajouter un tarif</a>
    </div>

    <form method="GET" class="flex flex-wrap gap-3 border-b border-slate-200 p-5">
        <select name="zone_id" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <option value="">Toutes les zones</option>
            @foreach ($zones as $zone)
                <option value="{{ $zone->id }}" @selected(request('zone_id') == $zone->id)>{{ $zone->nom }}</option>
            @endforeach
        </select>
        <select name="type_livraison" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <option value="">Tous les types</option>
            @foreach (['STANDARD', 'EXPRESS'] as $t)
                <option value="{{ $t }}" @selected(request('type_livraison') === $t)>{{ $t }}</option>
            @endforeach
        </select>
        <button class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">Filtrer</button>
        @if (request()->hasAny(['zone_id', 'type_livraison']))
            <a href="{{ route('tarifs.index') }}" class="px-2 py-2 text-sm text-slate-500 hover:underline">Réinitialiser</a>
        @endif
    </form>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-5 py-3">Zone</th>
                    <th class="px-5 py-3">Poids</th>
                    <th class="px-5 py-3">Type</th>
                    <th class="px-5 py-3">Priorité</th>
                    <th class="px-5 py-3 text-right">Prix</th>
                    <th class="px-5 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse ($tarifs as $tarif)
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-3 font-medium text-slate-800">{{ $tarif->zone->nom }}</td>
                        <td class="px-5 py-3">{{ $tarif->poids_min + 0 }} - {{ $tarif->poids_max + 0 }} kg</td>
                        <td class="px-5 py-3">{{ $tarif->type_livraison }}</td>
                        <td class="px-5 py-3">{{ $tarif->priorite }}</td>
                        <td class="px-5 py-3 text-right font-medium">{{ number_format($tarif->prix, 0, ',', ' ') }} Ar</td>
                        <td class="px-5 py-3 text-right">
                            <a href="{{ route('tarifs.edit', $tarif) }}" class="rounded-lg px-3 py-1 text-blue-600 hover:bg-blue-50">Modifier</a>
                            <form action="{{ route('tarifs.destroy', $tarif) }}" method="POST" class="inline"
                                  onsubmit="return confirm('Supprimer ce tarif ?')">
                                @csrf
                                @method('DELETE')
                                <button class="rounded-lg px-3 py-1 text-red-600 hover:bg-red-50">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-8 text-center text-slate-500">Aucun tarif trouvé.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="border-t border-slate-200 p-4">{{ $tarifs->links() }}</div>
</div>
@endsection