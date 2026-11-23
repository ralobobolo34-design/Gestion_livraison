@extends('layouts.app')

@section('titre', 'Passer une commande')

@section('contenu')
@php $lignes = old('colis', [[]]); @endphp

<form id="formulaire-commande" action="{{ route('commandes.store') }}" method="POST" class="max-w-4xl space-y-6">
    @csrf

    <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <h2 class="mb-4 text-base font-semibold text-slate-800">Votre commande</h2>

        <div class="grid gap-5 sm:grid-cols-2">

            <div>
                <p class="mb-1 text-sm font-medium text-slate-700">Commande au nom de</p>
                <p class="rounded-lg bg-slate-50 px-3 py-2 text-sm font-medium text-slate-800">
                    {{ $client->nom }} {{ $client->prenom }}
                    @if ($client->telephone) <span class="font-normal text-slate-500">· {{ $client->telephone }}</span> @endif
                </p>
                @error('client_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="quartier_id" class="mb-1 block text-sm font-medium text-slate-700">Quartier de destination</label>
                <select id="quartier_id" name="quartier_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">-- Choisir --</option>
                    @foreach ($zones as $zone)
                        <optgroup label="{{ $zone->nom }}">
                            @foreach ($zone->quartiers as $quartier)
                                <option value="{{ $quartier->id }}" @selected(old('quartier_id') == $quartier->id)>{{ $quartier->nom }}</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-slate-400">La zone et le tarif en découlent automatiquement.</p>
                @error('quartier_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="adresse_depart" class="mb-1 block text-sm font-medium text-slate-700">Adresse de départ (récupération)</label>
                <input type="text" id="adresse_depart" name="adresse_depart" value="{{ old('adresse_depart', $client->adresse) }}"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                @error('adresse_depart') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="adresse_destination" class="mb-1 block text-sm font-medium text-slate-700">Adresse précise de destination (rue, lot...)</label>
                <input type="text" id="adresse_destination" name="adresse_destination" value="{{ old('adresse_destination') }}"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                @error('adresse_destination') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="type_livraison" class="mb-1 block text-sm font-medium text-slate-700">Type de livraison</label>
                <select id="type_livraison" name="type_livraison" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    @foreach (['STANDARD', 'EXPRESS'] as $v)
                        <option value="{{ $v }}" @selected(old('type_livraison', 'STANDARD') === $v)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="priorite" class="mb-1 block text-sm font-medium text-slate-700">Priorité</label>
                <select id="priorite" name="priorite" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    @foreach (['NORMALE', 'URGENTE'] as $v)
                        <option value="{{ $v }}" @selected(old('priorite', 'NORMALE') === $v)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="date_livraison_prevue" class="mb-1 block text-sm font-medium text-slate-700">Date de livraison souhaitée</label>
                <input type="date" id="date_livraison_prevue" name="date_livraison_prevue" value="{{ old('date_livraison_prevue') }}"
                       min="{{ now()->toDateString() }}"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                @error('date_livraison_prevue') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>
    </div>

    <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-base font-semibold text-slate-800">Vos colis</h2>
            <button type="button" id="ajouter-colis"
                    class="rounded-lg bg-slate-800 px-3 py-1.5 text-sm font-medium text-white hover:bg-slate-700">
                + Ajouter un colis
            </button>
        </div>

        @error('colis') <p class="mb-3 text-sm text-red-600">{{ $message }}</p> @enderror

        <div id="colis-lignes" class="space-y-4">
            @foreach ($lignes as $i => $ligne)
                @include('commandes._ligne_colis', ['i' => $i, 'ligne' => $ligne])
            @endforeach
        </div>
    </div>

    {{-- Estimation du prix --}}
    <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <h2 class="text-base font-semibold text-slate-800">Estimation du prix</h2>
        <p id="estimation-etat" class="mt-2 text-sm text-slate-500">Choisissez un quartier et renseignez le poids des colis.</p>
        <p id="estimation-total" class="mt-2 hidden text-3xl font-bold text-slate-800"></p>
        <p class="mt-2 text-xs text-slate-400">Le prix dépend de la zone du quartier, du poids, du type de livraison et de la priorité. Il est recalculé à l'enregistrement.</p>
    </div>

    <p class="text-sm text-slate-500">Votre commande sera enregistrée « en attente » : notre équipe la confirmera après vérification.</p>

    <div class="flex gap-3">
        <button class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-medium text-white hover:bg-blue-700">Envoyer ma commande</button>
        <a href="{{ route('commandes.index') }}" class="rounded-lg px-5 py-2 text-sm text-slate-600 hover:bg-slate-100">Annuler</a>
    </div>
</form>

<template id="modele-colis">
    @include('commandes._ligne_colis', ['i' => '__INDEX__', 'ligne' => []])
</template>

<script>
    const formulaire = document.getElementById('formulaire-commande');
    const conteneur = document.getElementById('colis-lignes');
    const modele = document.getElementById('modele-colis');
    const etat = document.getElementById('estimation-etat');
    const totalAffiche = document.getElementById('estimation-total');

    let prochainIndex = {{ count($lignes) }};
    let minuteur = null;
    let version = 0;

    const ar = (v) => Number(v).toLocaleString('fr-FR') + ' Ar';

    // --- Ajouter / retirer un colis ---
    document.getElementById('ajouter-colis').addEventListener('click', () => {
        if (conteneur.children.length >= 10) { alert('Maximum 10 colis par commande.'); return; }
        conteneur.insertAdjacentHTML('beforeend', modele.innerHTML.replaceAll('__INDEX__', prochainIndex++));
        planifier();
    });

    conteneur.addEventListener('click', (e) => {
        if (e.target.classList.contains('retirer-colis') && conteneur.children.length > 1) {
            e.target.closest('.ligne-colis').remove();
            planifier();
        }
    });

    // --- Estimation du prix ---
    function afficherMessage(message) {
        etat.textContent = message;
        etat.classList.remove('hidden');
        totalAffiche.classList.add('hidden');
        conteneur.querySelectorAll('.prix-ligne').forEach((p) => { p.textContent = ''; });
    }

    async function estimer() {
        const quartier = formulaire.querySelector('[name=quartier_id]').value;
        const aDesPoids = [...formulaire.querySelectorAll('[name$="[poids]"]')].some((c) => parseFloat(c.value) > 0);

        if (!quartier || !aDesPoids) {
            afficherMessage('Choisissez un quartier et renseignez le poids des colis.');
            return;
        }

        const numero = ++version;

        try {
            const reponse = await fetch('{{ route('commandes.estimation') }}', {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: new FormData(formulaire),
            });

            if (numero !== version) return;   // une réponse plus récente existe déjà

            if (reponse.status === 422) { afficherMessage('Vérifiez les poids saisis (maximum 10 kg par colis).'); return; }
            if (!reponse.ok) throw new Error();

            const d = await reponse.json();
            let horsGrille = false;

            conteneur.querySelectorAll('.ligne-colis').forEach((ligne, i) => {
                const cible = ligne.querySelector('.prix-ligne');
                const info = d.lignes[i] || { statut: 'vide' };

                if (info.statut === 'ok') cible.textContent = ar(info.prix);
                else if (info.statut === 'hors_grille') { cible.textContent = 'Aucun tarif pour ce poids'; horsGrille = true; }
                else cible.textContent = '';
            });

            etat.textContent = 'Zone : ' + d.zone + (horsGrille ? ' · un colis n\'a pas de tarif, corrigez son poids.' : '');
            etat.classList.remove('hidden');
            totalAffiche.textContent = 'Total estimé : ' + ar(d.total);
            totalAffiche.classList.remove('hidden');
        } catch (e) {
            afficherMessage('Estimation indisponible pour le moment.');
        }
    }

    function planifier() {
        clearTimeout(minuteur);
        minuteur = setTimeout(estimer, 400);
    }

    formulaire.addEventListener('input', planifier);
    formulaire.addEventListener('change', planifier);
    estimer();
</script>
@endsection