@extends('layouts.app')

@section('titre', 'Colis')

@section('contenu')
<div class="rounded-xl bg-white shadow-sm ring-1 ring-slate-200">

    <h2 class="border-b border-slate-200 p-5 text-base font-semibold text-slate-800">Liste des colis</h2>

    <form method="GET" class="grid gap-3 border-b border-slate-200 p-5 sm:grid-cols-2 lg:grid-cols-4">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Référence, client, téléphone..."
               class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
        <select name="statut" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <option value="">Tous les statuts</option>
            @foreach (array_keys(\App\Services\ColisStatutService::TRANSITIONS) as $s)
                <option value="{{ $s }}" @selected(request('statut') === $s)>{{ str_replace('_', ' ', $s) }}</option>
            @endforeach
        </select>
        <input type="date" name="du" value="{{ request('du') }}" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
        <input type="date" name="au" value="{{ request('au') }}" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
        <div class="flex gap-3">
            <button class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">Filtrer</button>
            @if (request()->hasAny(['q', 'statut', 'du', 'au']))
                <a href="{{ route('colis.index') }}" class="px-2 py-2 text-sm text-slate-500 hover:underline">Réinitialiser</a>
            @endif
        </div>
    </form>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-5 py-3">Référence</th>
                    <th class="px-5 py-3">Client</th>
                    <th class="px-5 py-3">Téléphone</th>
                    <th class="px-5 py-3">Commande</th>
                    <th class="px-5 py-3">Créé le</th>
                    <th class="px-5 py-3">Statut</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse ($colis as $c)
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-3 font-medium text-slate-800">
                            <a href="{{ route('colis.show', $c) }}" class="hover:underline">{{ $c->reference }}</a>
                        </td>
                        <td class="px-5 py-3">{{ $c->commande->client->nom }} {{ $c->commande->client->prenom }}</td>
                        <td class="px-5 py-3">{{ $c->commande->client->telephone ?? '-' }}</td>
                        <td class="px-5 py-3">{{ $c->commande->reference }}</td>
                        <td class="px-5 py-3">{{ $c->created_at->format('d/m/Y') }}</td>
                        <td class="px-5 py-3"><x-badge :statut="$c->statut" /></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-8 text-center text-slate-500">Aucun colis trouvé.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="border-t border-slate-200 p-4">{{ $colis->links() }}</div>
</div>
@endsection