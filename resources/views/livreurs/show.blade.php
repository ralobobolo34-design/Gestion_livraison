@extends('layouts.app')

@section('titre', $livreur->nom . ' ' . $livreur->prenom)

@section('contenu')
<div class="mb-6 rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
    <dl class="grid gap-4 text-sm sm:grid-cols-2 lg:grid-cols-3">
        <div><dt class="text-slate-500">Téléphone</dt><dd class="font-medium">{{ $livreur->telephone }}</dd></div>
        <div><dt class="text-slate-500">Email</dt><dd class="font-medium">{{ $livreur->email ?? '-' }}</dd></div>
        <div><dt class="text-slate-500">Véhicule</dt><dd class="font-medium">{{ $livreur->type_vehicule }} {{ $livreur->numero_vehicule }}</dd></div>
        <div><dt class="text-slate-500">Statut</dt><dd class="font-medium">{{ $livreur->statut }}</dd></div>
        <div><dt class="text-slate-500">Inscrit le</dt><dd class="font-medium">{{ $livreur->date_inscription?->format('d/m/Y') ?? '-' }}</dd></div>
    </dl>
    @unless ($livreur->peutRecevoirAffectation())
        <p class="mt-4 rounded-lg bg-yellow-50 px-4 py-2 text-sm text-yellow-800">
            Ce livreur ne peut pas recevoir de nouvelle affectation.
        </p>
    @endunless
</div>

<div class="rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
    <h2 class="border-b border-slate-200 p-5 text-base font-semibold text-slate-800">Tournées</h2>
    <table class="w-full text-left text-sm">
        <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500">
            <tr>
                <th class="px-5 py-3">Référence</th>
                <th class="px-5 py-3">Date</th>
                <th class="px-5 py-3">Horaires</th>
                <th class="px-5 py-3">Colis</th>
                <th class="px-5 py-3">Statut</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">
            @forelse ($tournees as $tournee)
                <tr>
                    <td class="px-5 py-3 font-medium">{{ $tournee->reference }}</td>
                    <td class="px-5 py-3">{{ $tournee->date->format('d/m/Y') }}</td>
                    <td class="px-5 py-3">{{ substr($tournee->heure_depart, 0, 5) }} - {{ substr($tournee->heure_fin ?? '', 0, 5) }}</td>
                    <td class="px-5 py-3">{{ $tournee->colis_count }}</td>
                    <td class="px-5 py-3">{{ $tournee->statut }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-5 py-8 text-center text-slate-500">Aucune tournée.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection