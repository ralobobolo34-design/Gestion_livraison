@extends('layouts.app')

@section('titre', 'Livreurs')

@section('contenu')
<div class="rounded-xl bg-white shadow-sm ring-1 ring-slate-200">

    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 p-5">
        <h2 class="text-base font-semibold text-slate-800">Liste des livreurs</h2>
        <a href="{{ route('livreurs.create') }}"
           class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
            + Ajouter un livreur
        </a>
    </div>

    <form method="GET" class="flex flex-wrap gap-3 border-b border-slate-200 p-5">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Nom, téléphone, n° véhicule..."
               class="w-64 rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
        <select name="statut" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <option value="">Tous les statuts</option>
            @foreach (['ACTIF', 'INACTIF', 'INDISPONIBLE'] as $s)
                <option value="{{ $s }}" @selected(request('statut') === $s)>{{ $s }}</option>
            @endforeach
        </select>
        <select name="vehicule" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <option value="">Tous les véhicules</option>
            @foreach (['MOTO', 'VOITURE', 'CAMION', 'AUTRE'] as $v)
                <option value="{{ $v }}" @selected(request('vehicule') === $v)>{{ $v }}</option>
            @endforeach
        </select>
        <button class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
            Rechercher
        </button>
        @if (request()->hasAny(['q', 'statut', 'vehicule']))
            <a href="{{ route('livreurs.index') }}" class="px-2 py-2 text-sm text-slate-500 hover:underline">Réinitialiser</a>
        @endif
    </form>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-5 py-3">Nom</th>
                    <th class="px-5 py-3">Téléphone</th>
                    <th class="px-5 py-3">Véhicule</th>
                    <th class="px-5 py-3">Statut</th>
                    <th class="px-5 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse ($livreurs as $livreur)
                    @php
                        $couleurs = [
                            'ACTIF'        => 'bg-green-100 text-green-700',
                            'INACTIF'      => 'bg-slate-200 text-slate-600',
                            'INDISPONIBLE' => 'bg-yellow-100 text-yellow-700',
                        ];
                    @endphp
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-3 font-medium text-slate-800">
                            <a href="{{ route('livreurs.show', $livreur) }}" class="hover:underline">
                                {{ $livreur->nom }} {{ $livreur->prenom }}
                            </a>
                        </td>
                        <td class="px-5 py-3">{{ $livreur->telephone }}</td>
                        <td class="px-5 py-3">
                            {{ $livreur->type_vehicule }}
                            @if ($livreur->numero_vehicule)
                                <span class="text-slate-400">· {{ $livreur->numero_vehicule }}</span>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            <span class="rounded-full px-2.5 py-0.5 text-xs font-medium {{ $couleurs[$livreur->statut] }}">
                                {{ $livreur->statut }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-right">
                            <a href="{{ route('livreurs.edit', $livreur) }}"
                               class="rounded-lg px-3 py-1 text-blue-600 hover:bg-blue-50">Modifier</a>
                            @if ($livreur->statut !== 'INACTIF')
                                <form action="{{ route('livreurs.destroy', $livreur) }}" method="POST" class="inline"
                                      onsubmit="return confirm('Désactiver ce livreur ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="rounded-lg px-3 py-1 text-red-600 hover:bg-red-50">Désactiver</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-8 text-center text-slate-500">Aucun livreur trouvé.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="border-t border-slate-200 p-4">
        {{ $livreurs->links() }}
    </div>
</div>
@endsection