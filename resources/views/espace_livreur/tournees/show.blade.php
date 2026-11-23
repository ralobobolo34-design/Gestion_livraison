@extends('layouts.app')

@section('titre', $tournee->reference)

@section('contenu')
<div class="mb-6 rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <h2 class="text-lg font-semibold text-slate-800">{{ $tournee->reference }}</h2>
            <x-badge :statut="$tournee->statut" />
        </div>
        <a href="{{ route('livreur.tournees') }}" class="text-sm text-blue-600 hover:underline">← Mes tournées</a>
    </div>

    <dl class="grid gap-4 text-sm sm:grid-cols-3">
        <div><dt class="text-slate-500">Date</dt><dd class="font-medium">{{ $tournee->date->format('d/m/Y') }}</dd></div>
        <div><dt class="text-slate-500">Horaires</dt><dd class="font-medium">{{ substr($tournee->heure_depart, 0, 5) }} - {{ substr($tournee->heure_fin ?? '', 0, 5) }}</dd></div>
        <div><dt class="text-slate-500">Colis</dt><dd class="font-medium">{{ $tournee->colis->count() }}</dd></div>
    </dl>

    @if ($errors->any())
        <div class="mt-4 rounded-lg bg-red-100 px-4 py-3 text-sm text-red-800">
            @foreach ($errors->all() as $erreur)
                <p>{{ $erreur }}</p>
            @endforeach
        </div>
    @endif

    <div class="mt-5 flex flex-wrap gap-2 border-t border-slate-200 pt-4">
        @if ($tournee->statut === 'PLANIFIEE')
            @if ($peutDemarrer)
                <form action="{{ route('livreur.tournees.statut', $tournee) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="statut" value="EN_COURS">
                    <button class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">Démarrer la tournée</button>
                </form>
            @else
                <p class="text-sm text-slate-500">Cette tournée est prévue le {{ $tournee->date->format('d/m/Y') }} : vous pourrez la démarrer ce jour-là.</p>
            @endif
        @elseif ($tournee->statut === 'EN_COURS')
            <form action="{{ route('livreur.tournees.statut', $tournee) }}" method="POST"
                  onsubmit="return confirm('Terminer la tournée ?')">
                @csrf
                @method('PATCH')
                <input type="hidden" name="statut" value="TERMINEE">
                <button class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">Terminer la tournée</button>
            </form>
            <p class="self-center text-sm text-slate-500">Chaque colis doit être livré ou signalé en échec avant de terminer.</p>
        @endif
    </div>
</div>

<div class="space-y-4">
    @forelse ($tournee->colis->sortBy('pivot.ordre') as $colis)
        @php
            $commande   = $colis->commande;
            $client     = $commande->client;
            $derniere   = $colis->livraisons->sortByDesc('id')->first();
            $aLivrer    = $tournee->statut === 'EN_COURS' && $colis->statut === 'EN_LIVRAISON';
        @endphp

        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                <div class="flex items-center gap-3">
                    <span class="flex h-7 w-7 items-center justify-center rounded-full bg-slate-100 text-xs font-semibold text-slate-600">{{ $colis->pivot->ordre }}</span>
                    <span class="font-semibold text-slate-800">{{ $colis->reference }}</span>
                    @if ($colis->fragile)
                        <span class="rounded-full bg-orange-100 px-2 py-0.5 text-xs font-medium text-orange-700">Fragile</span>
                    @endif
                </div>
                <x-badge :statut="$colis->statut" />
            </div>

            <dl class="grid gap-3 text-sm sm:grid-cols-2">
                <div><dt class="text-slate-500">Destinataire</dt><dd class="font-medium">{{ $client->nom }} {{ $client->prenom }}</dd></div>
                <div><dt class="text-slate-500">Téléphone</dt>
                    <dd class="font-medium">
                        @if ($client->telephone)
                            <a href="tel:{{ preg_replace('/\s+/', '', $client->telephone) }}" class="text-blue-600 hover:underline">{{ $client->telephone }}</a>
                        @else - @endif
                    </dd></div>
                <div class="sm:col-span-2"><dt class="text-slate-500">Adresse de livraison</dt>
                    <dd class="font-medium">{{ $commande->adresse_destination }}
                        <span class="text-slate-400">· {{ $commande->quartier?->nom }} ({{ $commande->zone->nom }})</span></dd></div>
                <div><dt class="text-slate-500">Colis</dt><dd class="font-medium">{{ $colis->description ?? '-' }} · {{ $colis->poids + 0 }} kg</dd></div>
                <div><dt class="text-slate-500">Commande</dt><dd class="font-medium">{{ $commande->reference }} · {{ $commande->type_livraison }} / {{ $commande->priorite }}</dd></div>
            </dl>

            {{-- Résultat déjà enregistré --}}
            @if ($colis->statut === 'LIVRE' && $derniere)
                <div class="mt-4 rounded-lg bg-green-50 px-4 py-3 text-sm text-green-800">
                    Livré à <strong>{{ $derniere->nom_receptionnaire }}</strong> le {{ $derniere->date_livraison?->format('d/m/Y à H:i') }}.
                    @if ($derniere->commentaire) <span class="block text-green-700">{{ $derniere->commentaire }}</span> @endif
                    <span class="mt-1 flex gap-3 text-xs">
                        @if ($derniere->photo) <a class="underline" target="_blank" href="{{ asset('storage/' . $derniere->photo) }}">Photo</a> @endif
                        @if ($derniere->signature) <a class="underline" target="_blank" href="{{ asset('storage/' . $derniere->signature) }}">Signature</a> @endif
                        @if ($derniere->latitude) <span>GPS : {{ $derniere->latitude }}, {{ $derniere->longitude }}</span> @endif
                    </span>
                </div>
            @elseif ($colis->statut === 'ECHEC_LIVRAISON' && $derniere)
                <div class="mt-4 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-800">
                    Échec : <strong>{{ str_replace('_', ' ', $derniere->motif_echec) }}</strong>
                    @if ($derniere->commentaire) <span class="block">{{ $derniere->commentaire }}</span> @endif
                </div>
            @endif

            {{-- Actions --}}
            @if ($aLivrer)
                <div class="mt-4 space-y-3">

                    <details class="livraison rounded-lg border border-slate-200">
                        <summary class="cursor-pointer rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">
                            Confirmer la livraison
                        </summary>
                        <form action="{{ route('livreur.colis.livrer', $colis) }}" method="POST" enctype="multipart/form-data" class="space-y-4 p-4">
                            @csrf
                            <div>
                                <label class="mb-1 block text-sm font-medium text-slate-700">Nom de la personne qui réceptionne</label>
                                <input type="text" name="nom_receptionnaire" required
                                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-slate-700">Commentaire (facultatif)</label>
                                <textarea name="commentaire" rows="2" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-slate-700">Photo (facultatif)</label>
                                <input type="file" name="photo" accept="image/*" capture="environment" class="block w-full text-sm text-slate-600">
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-slate-700">Signature (facultatif)</label>
                                <canvas class="signature-pad h-32 w-full touch-none rounded-lg border border-slate-300 bg-white"></canvas>
                                <input type="hidden" name="signature">
                                <button type="button" class="effacer-signature mt-1 text-xs text-slate-500 hover:underline">Effacer la signature</button>
                            </div>
                            <div class="flex items-center gap-3">
                                <button type="button" class="gps-btn rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-600 hover:bg-slate-50">
                                    Utiliser ma position
                                </button>
                                <span class="gps-etat text-xs text-slate-500"></span>
                                <input type="hidden" name="latitude">
                                <input type="hidden" name="longitude">
                            </div>
                            <button class="rounded-lg bg-green-600 px-5 py-2 text-sm font-medium text-white hover:bg-green-700">Valider la livraison</button>
                        </form>
                    </details>

                    <details class="livraison rounded-lg border border-slate-200">
                        <summary class="cursor-pointer rounded-lg bg-red-50 px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-100">
                            Signaler un échec
                        </summary>
                        <form action="{{ route('livreur.colis.echec', $colis) }}" method="POST" class="space-y-4 p-4">
                            @csrf
                            <div>
                                <label class="mb-1 block text-sm font-medium text-slate-700">Motif</label>
                                <select name="motif_echec" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                    @foreach (\App\Models\Livraison::MOTIFS as $motif)
                                        <option value="{{ $motif }}">{{ str_replace('_', ' ', $motif) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-slate-700">Commentaire (obligatoire si « AUTRE »)</label>
                                <textarea name="commentaire" rows="2" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                            </div>
                            <button class="rounded-lg bg-red-600 px-5 py-2 text-sm font-medium text-white hover:bg-red-700">Enregistrer l'échec</button>
                        </form>
                    </details>
                </div>
            @elseif ($tournee->statut === 'PLANIFIEE' && $colis->statut === 'EN_LIVRAISON')
                <p class="mt-4 text-sm text-slate-500">Démarrez la tournée pour pouvoir livrer ce colis.</p>
            @endif
        </div>
    @empty
        <p class="rounded-xl bg-white p-6 text-sm text-slate-500 shadow-sm ring-1 ring-slate-200">Aucun colis dans cette tournée.</p>
    @endforelse
</div>

<script>
    // Signature dessinée au doigt ou à la souris, et position GPS
    document.querySelectorAll('details.livraison').forEach((details) => {
        const canvas = details.querySelector('.signature-pad');
        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        const champ = details.querySelector('input[name=signature]');
        let initialise = false, dessine = false, trace = false;

        details.addEventListener('toggle', () => {
            if (!details.open || initialise) return;
            canvas.width = canvas.offsetWidth;
            canvas.height = canvas.offsetHeight;
            ctx.lineWidth = 2;
            ctx.lineCap = 'round';
            ctx.strokeStyle = '#0f172a';
            initialise = true;
        });

        const position = (e) => {
            const r = canvas.getBoundingClientRect();
            return [e.clientX - r.left, e.clientY - r.top];
        };

        canvas.addEventListener('pointerdown', (e) => {
            dessine = true;
            ctx.beginPath();
            ctx.moveTo(...position(e));
            canvas.setPointerCapture(e.pointerId);
        });
        canvas.addEventListener('pointermove', (e) => {
            if (!dessine) return;
            ctx.lineTo(...position(e));
            ctx.stroke();
            trace = true;
        });
        ['pointerup', 'pointerleave'].forEach((nom) => canvas.addEventListener(nom, () => { dessine = false; }));

        details.querySelector('.effacer-signature').addEventListener('click', () => {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            trace = false;
            champ.value = '';
        });

        details.querySelector('form').addEventListener('submit', () => {
            champ.value = trace ? canvas.toDataURL('image/png') : '';
        });

        details.querySelector('.gps-btn').addEventListener('click', () => {
            const etat = details.querySelector('.gps-etat');
            if (!navigator.geolocation) { etat.textContent = 'GPS indisponible.'; return; }
            etat.textContent = 'Localisation en cours...';
            navigator.geolocation.getCurrentPosition(
                (p) => {
                    details.querySelector('[name=latitude]').value = p.coords.latitude.toFixed(7);
                    details.querySelector('[name=longitude]').value = p.coords.longitude.toFixed(7);
                    etat.textContent = 'Position enregistrée.';
                },
                () => { etat.textContent = 'Position refusée ou indisponible.'; }
            );
        });
    });
</script>
@endsection