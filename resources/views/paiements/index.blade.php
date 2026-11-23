@extends('layouts.app')

@section('titre', 'Paiements')

@section('contenu')
@php $estStaff = auth()->user()->isStaff(); @endphp

{{-- Client : commandes à régler --}}
@unless ($estStaff)
    <div class="mb-6 rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
        <h2 class="border-b border-slate-200 p-5 text-base font-semibold text-slate-800">Commandes à régler</h2>
        <div class="divide-y divide-slate-200">
            @forelse ($aRegler as $commande)
                <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-4 text-sm">
                    <div>
                        <a href="{{ route('commandes.show', $commande) }}" class="font-medium text-slate-800 hover:underline">{{ $commande->reference }}</a>
                        <p class="text-xs text-slate-500">
                            Total {{ number_format($commande->montant, 0, ',', ' ') }} Ar · commandée le {{ $commande->date_commande->format('d/m/Y') }}
                        </p>
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="font-semibold text-orange-600">Reste {{ number_format($commande->montantRestant(), 0, ',', ' ') }} Ar</span>
                        <a href="{{ route('paiements.payer', $commande) }}"
                           class="rounded-full bg-blue-600 px-5 py-2 text-sm font-medium text-white hover:bg-blue-700">Payer</a>
                    </div>
                </div>
            @empty
                <p class="px-5 py-8 text-center text-sm text-slate-500">Aucune commande à régler pour le moment.</p>
            @endforelse
        </div>
    </div>
@endunless

<div class="rounded-xl bg-white shadow-sm ring-1 ring-slate-200">

    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 p-5">
        <h2 class="text-base font-semibold text-slate-800">{{ $estStaff ? 'Liste des paiements' : 'Mes paiements' }}</h2>
        @if ($estStaff)
            <a href="{{ route('paiements.create') }}"
               class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">+ Enregistrer un paiement</a>
        @endif
    </div>

    @error('statut')
        <p class="m-5 rounded-lg bg-red-100 px-4 py-2 text-sm text-red-800">{{ $message }}</p>
    @enderror

    <form method="GET" class="grid gap-3 border-b border-slate-200 p-5 sm:grid-cols-2 lg:grid-cols-4">
        <input type="text" name="q" value="{{ request('q') }}"
               placeholder="{{ $estStaff ? 'Référence, transaction, commande, client...' : 'Référence, commande...' }}"
               class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
        <select name="mode" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <option value="">Tous les modes</option>
            @foreach (['ESPECES', 'MOBILE_MONEY', 'CARTE', 'PAIEMENT_LIVRAISON'] as $m)
                <option value="{{ $m }}" @selected(request('mode') === $m)>{{ str_replace('_', ' ', $m) }}</option>
            @endforeach
        </select>
        <select name="statut" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <option value="">Tous les statuts</option>
            @foreach (['EN_ATTENTE', 'PAYE', 'PARTIEL', 'ANNULE'] as $s)
                <option value="{{ $s }}" @selected(request('statut') === $s)>{{ str_replace('_', ' ', $s) }}</option>
            @endforeach
        </select>
        <div class="flex gap-2">
            <input type="date" name="du" value="{{ request('du') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <input type="date" name="au" value="{{ request('au') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
        </div>
        <div class="flex gap-3">
            <button class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">Filtrer</button>
            @if (request()->hasAny(['q', 'mode', 'statut', 'du', 'au']))
                <a href="{{ route('paiements.index') }}" class="px-2 py-2 text-sm text-slate-500 hover:underline">Réinitialiser</a>
            @endif
        </div>
    </form>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-5 py-3">Référence</th>
                    <th class="px-5 py-3">Commande</th>
                    @if ($estStaff) <th class="px-5 py-3">Client</th> @endif
                    <th class="px-5 py-3">Mode</th>
                    <th class="px-5 py-3">Transaction</th>
                    <th class="px-5 py-3">Date</th>
                    <th class="px-5 py-3 text-right">Montant</th>
                    <th class="px-5 py-3">Statut</th>
                    <th class="px-5 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse ($paiements as $paiement)
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-3 font-medium text-slate-800">{{ $paiement->reference }}</td>
                        <td class="px-5 py-3">
                            <a href="{{ route('commandes.show', $paiement->commande) }}" class="hover:underline">{{ $paiement->commande->reference }}</a>
                        </td>
                        @if ($estStaff)
                            <td class="px-5 py-3">{{ $paiement->commande->client->nom }} {{ $paiement->commande->client->prenom }}</td>
                        @endif
                        <td class="px-5 py-3">
                            {{ str_replace('_', ' ', $paiement->mode) }}
                            @if ($paiement->operateur)
                                <span class="block text-xs text-slate-400">{{ str_replace('_', ' ', $paiement->operateur) }}</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 font-mono text-xs">{{ $paiement->reference_transaction ?? '-' }}</td>
                        <td class="px-5 py-3">{{ $paiement->date_paiement->format('d/m/Y H:i') }}</td>
                        <td class="px-5 py-3 text-right">{{ number_format($paiement->montant, 0, ',', ' ') }} Ar</td>
                        <td class="px-5 py-3">
                            <x-badge :statut="$paiement->statut" />
                            @if ($paiement->statut === 'EN_ATTENTE')
                                <span class="mt-1 block text-xs text-slate-400">{{ $estStaff ? 'à vérifier' : 'en vérification' }}</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-right">
                            @if ($estStaff)
                                @if ($paiement->statut === 'EN_ATTENTE')
                                    <form action="{{ route('paiements.confirmer', $paiement) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button class="rounded-lg px-3 py-1 text-green-700 hover:bg-green-50">Confirmer</button>
                                    </form>
                                @endif
                                @if ($paiement->statut !== 'ANNULE')
                                    <form action="{{ route('paiements.annuler', $paiement) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Annuler ce paiement ?')">
                                        @csrf
                                        @method('PATCH')
                                        <button class="rounded-lg px-3 py-1 text-red-600 hover:bg-red-50">Annuler</button>
                                    </form>
                                @endif
                            @else
                                @can('retirer', $paiement)
                                    <form action="{{ route('paiements.retirer', $paiement) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Retirer cette déclaration de paiement ?')">
                                        @csrf
                                        @method('PATCH')
                                        <button class="rounded-lg px-3 py-1 text-red-600 hover:bg-red-50">Retirer</button>
                                    </form>
                                @endcan
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="{{ $estStaff ? 9 : 8 }}" class="px-5 py-8 text-center text-slate-500">Aucun paiement trouvé.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="border-t border-slate-200 p-4">{{ $paiements->links() }}</div>
</div>
@endsection