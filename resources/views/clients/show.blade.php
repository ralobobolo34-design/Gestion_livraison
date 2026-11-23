@extends('layouts.app')

@section('titre', $client->nom . ' ' . $client->prenom)

@section('contenu')
<div class="mb-6 rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
    <dl class="grid gap-4 text-sm sm:grid-cols-2">
        <div><dt class="text-slate-500">Téléphone</dt><dd class="font-medium">{{ $client->telephone ?? '-' }}</dd></div>
        <div><dt class="text-slate-500">Email</dt><dd class="font-medium">{{ $client->email ?? '-' }}</dd></div>
        <div><dt class="text-slate-500">Adresse</dt><dd class="font-medium">{{ $client->adresse ?? '-' }}</dd></div>
        <div><dt class="text-slate-500">Statut</dt><dd class="font-medium">{{ $client->statut }}</dd></div>
    </dl>
</div>

<div class="rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
    <h2 class="border-b border-slate-200 p-5 text-base font-semibold text-slate-800">Historique des commandes</h2>
    <table class="w-full text-left text-sm">
        <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500">
            <tr>
                <th class="px-5 py-3">Référence</th>
                <th class="px-5 py-3">Date</th>
                <th class="px-5 py-3">Zone</th>
                <th class="px-5 py-3">Montant</th>
                <th class="px-5 py-3">Statut</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">
            @forelse ($commandes as $commande)
                <tr>
                    <td class="px-5 py-3 font-medium">{{ $commande->reference }}</td>
                    <td class="px-5 py-3">{{ $commande->date_commande->format('d/m/Y') }}</td>
                    <td class="px-5 py-3">{{ $commande->zone->nom }}</td>
                    <td class="px-5 py-3">{{ number_format($commande->montant, 0, ',', ' ') }} Ar</td>
                    <td class="px-5 py-3">{{ $commande->statut }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-5 py-8 text-center text-slate-500">Aucune commande.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection