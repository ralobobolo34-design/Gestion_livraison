@extends('layouts.app')

@section('titre', 'Clients')

@section('contenu')
<div class="rounded-xl bg-white shadow-sm ring-1 ring-slate-200">

    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 p-5">
        <h2 class="text-base font-semibold text-slate-800">Liste des clients</h2>
        <a href="{{ route('clients.create') }}"
           class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
            + Ajouter un client
        </a>
    </div>

    {{-- Recherche --}}
    <form method="GET" class="flex flex-wrap gap-3 border-b border-slate-200 p-5">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Nom, téléphone, e-mail..."
               class="w-64 rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
        <select name="statut" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <option value="">Tous les statuts</option>
            <option value="ACTIF" @selected(request('statut') === 'ACTIF')>Actif</option>
            <option value="INACTIF" @selected(request('statut') === 'INACTIF')>Inactif</option>
        </select>
        <button class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
            Rechercher
        </button>
        @if (request()->hasAny(['q', 'statut']))
            <a href="{{ route('clients.index') }}" class="px-2 py-2 text-sm text-slate-500 hover:underline">Réinitialiser</a>
        @endif
    </form>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-5 py-3">Nom</th>
                    <th class="px-5 py-3">Téléphone</th>
                    <th class="px-5 py-3">Email</th>
                    <th class="px-5 py-3">Statut</th>
                    <th class="px-5 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse ($clients as $client)
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-3 font-medium text-slate-800">
                            <a href="{{ route('clients.show', $client) }}" class="hover:underline">
                                {{ $client->nom }} {{ $client->prenom }}
                            </a>
                        </td>
                        <td class="px-5 py-3">{{ $client->telephone ?? '-' }}</td>
                        <td class="px-5 py-3">{{ $client->email ?? '-' }}</td>
                        <td class="px-5 py-3">
                            <span class="rounded-full px-2.5 py-0.5 text-xs font-medium
                                {{ $client->statut === 'ACTIF' ? 'bg-green-100 text-green-700' : 'bg-slate-200 text-slate-600' }}">
                                {{ $client->statut }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-right">
                            <a href="{{ route('clients.edit', $client) }}"
                               class="rounded-lg px-3 py-1 text-blue-600 hover:bg-blue-50">Modifier</a>
                            @if ($client->statut === 'ACTIF')
                                <form action="{{ route('clients.destroy', $client) }}" method="POST" class="inline"
                                      onsubmit="return confirm('Désactiver ce client ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="rounded-lg px-3 py-1 text-red-600 hover:bg-red-50">Désactiver</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-8 text-center text-slate-500">Aucun client trouvé.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="border-t border-slate-200 p-4">
        {{ $clients->links() }}
    </div>
</div>
@endsection