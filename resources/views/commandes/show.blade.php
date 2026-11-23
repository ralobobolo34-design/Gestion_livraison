@extends('layouts.app')

@section('titre', $commande->reference)

@section('contenu')
@php $estStaff = auth()->user()->isStaff(); @endphp

<div class="mb-6 rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <h2 class="text-lg font-semibold text-slate-800">{{ $commande->reference }}</h2>
            <x-badge :statut="$commande->statut" />
        </div>
        @if ($estStaff && in_array($commande->statut, ['EN_ATTENTE', 'CONFIRMEE']))
            <a href="{{ route('commandes.edit', $commande) }}" class="rounded-lg px-3 py-1.5 text-sm text-blue-600 hover:bg-blue-50">Modifier</a>
        @endif
        @if (auth()->user()->isClient() && $commande->statut !== 'ANNULEE' && $commande->montantRestant() > 0)
    <a href="{{ route('paiements.payer', $commande) }}"
       class="rounded-full bg-blue-600 px-5 py-2 text-sm font-medium text-white hover:bg-blue-700">Payer cette commande</a>
@endif
    </div>

    <dl class="grid gap-4 text-sm sm:grid-cols-2 lg:grid-cols-3">
        <div><dt class="text-slate-500">Client</dt>
            <dd class="font-medium">
                @if ($estStaff)
                    <a href="{{ route('clients.show', $commande->client) }}" class="hover:underline">{{ $commande->client->nom }} {{ $commande->client->prenom }}</a>
                @else
                    {{ $commande->client->nom }} {{ $commande->client->prenom }}
                @endif
            </dd></div>
        <div><dt class="text-slate-500">Quartier / zone</dt>
            <dd class="font-medium">{{ $commande->quartier?->nom ?? '-' }} · {{ $commande->zone->nom }}</dd></div>
        <div><dt class="text-slate-500">Type / priorité</dt><dd class="font-medium">{{ $commande->type_livraison }} / {{ $commande->priorite }}</dd></div>
        <div><dt class="text-slate-500">Départ</dt><dd class="font-medium">{{ $commande->adresse_depart }}</dd></div>
        <div><dt class="text-slate-500">Destination</dt><dd class="font-medium">{{ $commande->adresse_destination }}</dd></div>
        <div><dt class="text-slate-500">Date de commande</dt><dd class="font-medium">{{ $commande->date_commande->format('d/m/Y') }}</dd></div>
        <div><dt class="text-slate-500">Livraison prévue</dt><dd class="font-medium">{{ $commande->date_livraison_prevue?->format('d/m/Y') ?? '-' }}</dd></div>
        <div><dt class="text-slate-500">Montant</dt><dd class="font-medium">{{ number_format($commande->montant, 0, ',', ' ') }} Ar</dd></div>
        <div><dt class="text-slate-500">Encaissé</dt><dd class="font-medium">{{ number_format($commande->montantPaye(), 0, ',', ' ') }} Ar</dd></div>
        <div><dt class="text-slate-500">Reste à régler</dt><dd class="font-medium">{{ number_format($commande->montantRestant(), 0, ',', ' ') }} Ar</dd></div>
        <div><dt class="text-slate-500">En vérification</dt>
    <dd class="font-medium">{{ number_format($commande->paiements->where('statut', 'EN_ATTENTE')->sum('montant'), 0, ',', ' ') }} Ar</dd></div>
        <div><dt class="text-slate-500">Paiement</dt><dd class="font-medium"><x-badge :statut="$commande->statutPaiement()" /></dd></div>
    </dl>

    @error('statut')
        <p class="mt-4 rounded-lg bg-red-100 px-4 py-2 text-sm text-red-800">{{ $message }}</p>
    @enderror

    @if ($estStaff && $commande->statutsSuivants())
        <div class="mt-5 flex flex-wrap gap-2 border-t border-slate-200 pt-4">
            @foreach ($commande->statutsSuivants() as $suivant)
                <form action="{{ route('commandes.statut', $commande) }}" method="POST"
                      @if ($suivant === 'ANNULEE') onsubmit="return confirm('Annuler cette commande et tous ses colis ?')" @endif>
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="statut" value="{{ $suivant }}">
                    <button class="rounded-lg px-4 py-2 text-sm font-medium
                        {{ $suivant === 'ANNULEE' ? 'bg-red-50 text-red-700 hover:bg-red-100' : 'bg-blue-600 text-white hover:bg-blue-700' }}">
                        {{ $suivant === 'ANNULEE' ? 'Annuler la commande' : '→ ' . str_replace('_', ' ', $suivant) }}
                    </button>
                </form>
            @endforeach
        </div>
    @endif
</div>

<div class="mb-6 rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
    <h2 class="border-b border-slate-200 p-5 text-base font-semibold text-slate-800">Colis ({{ $commande->colis->count() }})</h2>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-5 py-3">Référence</th>
                    <th class="px-5 py-3">Description</th>
                    <th class="px-5 py-3">Poids</th>
                    <th class="px-5 py-3">Fragile</th>
                    <th class="px-5 py-3 text-right">Prix</th>
                    <th class="px-5 py-3">Statut</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @foreach ($commande->colis as $colis)
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-3 font-medium"><a href="{{ route('colis.show', $colis) }}" class="hover:underline">{{ $colis->reference }}</a></td>
                        <td class="px-5 py-3">{{ $colis->description ?? '-' }}</td>
                        <td class="px-5 py-3">{{ $colis->poids + 0 }} kg</td>
                        <td class="px-5 py-3">{{ $colis->fragile ? 'Oui' : 'Non' }}</td>
                        <td class="px-5 py-3 text-right">{{ number_format($colis->prix, 0, ',', ' ') }} Ar</td>
                        <td class="px-5 py-3"><x-badge :statut="$colis->statut" /></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
    <div class="flex items-center justify-between border-b border-slate-200 p-5">
        <h2 class="text-base font-semibold text-slate-800">Paiements</h2>
        @if ($estStaff && $commande->statut !== 'ANNULEE' && $commande->montantRestant() > 0)
            <a href="{{ route('paiements.create', ['commande_id' => $commande->id]) }}"
               class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">+ Enregistrer un paiement</a>
        @endif
        @can('annuler', $commande)
    <div class="mt-5 flex flex-wrap items-center gap-3 border-t border-slate-200 pt-4">
        <form action="{{ route('commandes.annuler', $commande) }}" method="POST"
              onsubmit="return confirm('Annuler cette commande ?')">
            @csrf
            @method('PATCH')
            <button class="rounded-lg bg-red-50 px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-100">Annuler ma commande</button>
        </form>
        <p class="text-sm text-slate-500">Vous pouvez annuler tant que la commande n'est pas confirmée.</p>
    </div>
@endcan
    </div>
    <table class="w-full text-left text-sm">
        <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500">
            <tr>
                <th class="px-5 py-3">Référence</th>
                <th class="px-5 py-3">Mode</th>
                <th class="px-5 py-3">Date</th>
                <th class="px-5 py-3 text-right">Montant</th>
                <th class="px-5 py-3">Statut</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">
            @forelse ($commande->paiements as $paiement)
                <tr>
                    <td class="px-5 py-3 font-medium">{{ $paiement->reference }}</td>
                    <td class="px-5 py-3">{{ str_replace('_', ' ', $paiement->mode) }}</td>
                    <td class="px-5 py-3">{{ $paiement->date_paiement->format('d/m/Y H:i') }}</td>
                    <td class="px-5 py-3 text-right">{{ number_format($paiement->montant, 0, ',', ' ') }} Ar</td>
                    <td class="px-5 py-3"><x-badge :statut="$paiement->statut" /></td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-5 py-8 text-center text-slate-500">Aucun paiement.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection