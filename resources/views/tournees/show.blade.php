@extends('layouts.app')

@section('titre', $tournee->reference)

@section('contenu')
<div class="mb-6 rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
    <div class="mb-4 flex items-center gap-3">
        <h2 class="text-lg font-semibold text-slate-800">{{ $tournee->reference }}</h2>
        <x-badge :statut="$tournee->statut" />
    </div>

    <dl class="grid gap-4 text-sm sm:grid-cols-2 lg:grid-cols-4">
        <div><dt class="text-slate-500">Livreur</dt>
            <dd class="font-medium"><a href="{{ route('livreurs.show', $tournee->livreur) }}" class="hover:underline">{{ $tournee->livreur->nom }} {{ $tournee->livreur->prenom }}</a></dd></div>
        <div><dt class="text-slate-500">Véhicule</dt><dd class="font-medium">{{ $tournee->livreur->type_vehicule }} {{ $tournee->livreur->numero_vehicule }}</dd></div>
        <div><dt class="text-slate-500">Date</dt><dd class="font-medium">{{ $tournee->date->format('d/m/Y') }}</dd></div>
        <div><dt class="text-slate-500">Horaires</dt><dd class="font-medium">{{ substr($tournee->heure_depart, 0, 5) }} - {{ substr($tournee->heure_fin ?? '', 0, 5) }}</dd></div>
    </dl>

    @if ($errors->any())
        <div class="mt-4 rounded-lg bg-red-100 px-4 py-3 text-sm text-red-800">
            @foreach ($errors->all() as $erreur)
                <p>{{ $erreur }}</p>
            @endforeach
        </div>
    @endif

    @if ($tournee->statutsSuivants())
        <div class="mt-5 flex flex-wrap gap-2 border-t border-slate-200 pt-4">
            @foreach ($tournee->statutsSuivants() as $suivant)
                <form action="{{ route('tournees.statut', $tournee) }}" method="POST"
                      @if ($suivant === 'ANNULEE') onsubmit="return confirm('Annuler cette tournée ?')" @endif>
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="statut" value="{{ $suivant }}">
                    <button class="rounded-lg px-4 py-2 text-sm font-medium
                        {{ $suivant === 'ANNULEE' ? 'bg-red-50 text-red-700 hover:bg-red-100' : 'bg-blue-600 text-white hover:bg-blue-700' }}">
                        @switch($suivant)
                            @case('EN_COURS') Démarrer la tournée @break
                            @case('TERMINEE') Terminer la tournée @break
                            @default Annuler la tournée
                        @endswitch
                    </button>
                </form>
            @endforeach
        </div>
    @endif
</div>

@if ($tournee->statut === 'PLANIFIEE')
    <div class="mb-6 rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <h2 class="mb-3 text-base font-semibold text-slate-800">Ajouter un colis</h2>

        @if ($disponibles->isEmpty())
            <p class="text-sm text-slate-500">Aucun colis disponible. Seuls les colis « ARRIVE AGENCE » (ou en échec de livraison) d'une commande non annulée, et pas déjà dans une tournée active, peuvent être affectés.</p>
        @else
            <form action="{{ route('tournees.colis.ajouter', $tournee) }}" method="POST" class="flex flex-wrap gap-3">
                @csrf
                <select name="colis_id" class="min-w-72 flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">-- Choisir un colis --</option>
                    @foreach ($disponibles as $c)
                        <option value="{{ $c->id }}">
                            {{ $c->reference }} · {{ $c->commande->client->nom }} · {{ $c->poids + 0 }} kg · {{ str_replace('_', ' ', $c->statut) }}
                        </option>
                    @endforeach
                </select>
                <button class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">Ajouter</button>
            </form>
        @endif
    </div>
@endif

<div class="rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
    <h2 class="border-b border-slate-200 p-5 text-base font-semibold text-slate-800">Colis de la tournée ({{ $tournee->colis->count() }})</h2>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-5 py-3">N°</th>
                    <th class="px-5 py-3">Colis</th>
                    <th class="px-5 py-3">Client</th>
                    <th class="px-5 py-3">Destination</th>
                    <th class="px-5 py-3">Zone</th>
                    <th class="px-5 py-3">Statut</th>
                    <th class="px-5 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse ($tournee->colis->sortBy('pivot.ordre') as $colis)
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-3">{{ $colis->pivot->ordre }}</td>
                        <td class="px-5 py-3 font-medium text-slate-800">
                            <a href="{{ route('colis.show', $colis) }}" class="hover:underline">{{ $colis->reference }}</a>
                        </td>
                        <td class="px-5 py-3">{{ $colis->commande->client->nom }} {{ $colis->commande->client->prenom }}<br>
                            <span class="text-xs text-slate-400">{{ $colis->commande->client->telephone }}</span></td>
                        <td class="px-5 py-3">{{ $colis->commande->adresse_destination }}</td>
                        <td class="px-5 py-3">{{ $colis->commande->zone->nom }}</td>
                        <td class="px-5 py-3"><x-badge :statut="$colis->statut" /></td>
                        <td class="px-5 py-3 text-right">
                            @if ($tournee->statut === 'PLANIFIEE')
                                <form action="{{ route('tournees.colis.retirer', [$tournee, $colis]) }}" method="POST" class="inline"
                                      onsubmit="return confirm('Retirer ce colis de la tournée ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="rounded-lg px-3 py-1 text-red-600 hover:bg-red-50">Retirer</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-5 py-8 text-center text-slate-500">Aucun colis dans cette tournée.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection