@extends('layouts.app')

@section('titre', 'Commandes')

@section('contenu')
<div class="rounded-xl bg-white shadow-sm ring-1 ring-slate-200">

    @if (auth()->user()->isClient())
    <a href="{{ route('commandes.create') }}"
       class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
        + Nouvelle commande
    </a>
@endif
    <form method="GET" class="grid gap-3 border-b border-slate-200 p-5 sm:grid-cols-2 lg:grid-cols-4">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Référence, client, téléphone..."
               class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
        <select name="statut" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <option value="">Tous les statuts</option>
            @foreach (array_keys(\App\Models\Commande::TRANSITIONS) as $s)
                <option value="{{ $s }}" @selected(request('statut') === $s)>{{ str_replace('_', ' ', $s) }}</option>
            @endforeach
        </select>
        <input type="date" name="du" value="{{ request('du') }}" title="Du"
               class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
        <input type="date" name="au" value="{{ request('au') }}" title="Au"
               class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
        <input type="number" name="montant_min" value="{{ request('montant_min') }}" placeholder="Montant min (Ar)"
               class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
        <input type="number" name="montant_max" value="{{ request('montant_max') }}" placeholder="Montant max (Ar)"
               class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
        <div class="flex gap-3">
            <button class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">Filtrer</button>
            @if (request()->hasAny(['q', 'statut', 'du', 'au', 'montant_min', 'montant_max']))
                <a href="{{ route('commandes.index') }}" class="px-2 py-2 text-sm text-slate-500 hover:underline">Réinitialiser</a>
            @endif
        </div>
    </form>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-5 py-3">Référence</th>
                    <th class="px-5 py-3">Client</th>
                    <th class="px-5 py-3">Zone</th>
                    <th class="px-5 py-3">Date</th>
                    <th class="px-5 py-3">Colis</th>
                    <th class="px-5 py-3 text-right">Montant</th>
                    <th class="px-5 py-3">Statut</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse ($commandes as $commande)
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-3 font-medium text-slate-800">
                            <a href="{{ route('commandes.show', $commande) }}" class="hover:underline">{{ $commande->reference }}</a>
                        </td>
                        <td class="px-5 py-3">{{ $commande->client->nom }} {{ $commande->client->prenom }}</td>
                        <td class="px-5 py-3">{{ $commande->zone->nom }}</td>
                        <td class="px-5 py-3">{{ $commande->date_commande->format('d/m/Y') }}</td>
                        <td class="px-5 py-3">{{ $commande->colis_count }}</td>
                        <td class="px-5 py-3 text-right">{{ number_format($commande->montant, 0, ',', ' ') }} Ar</td>
                        <td class="px-5 py-3"><x-badge :statut="$commande->statut" /></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-5 py-8 text-center text-slate-500">Aucune commande trouvée.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="border-t border-slate-200 p-4">{{ $commandes->links() }}</div>
</div>
@endsection