<?php

namespace App\Http\Requests;

use App\Models\Quartier;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ZoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('zone')?->id;

        return [
            'nom'         => ['required', 'string', 'max:100', Rule::unique('zones', 'nom')->ignore($id)],
            'description' => ['nullable', 'string', 'max:500'],
            'tarif_base'  => ['required', 'integer', 'min:0'],
            'statut'      => ['required', Rule::in(['ACTIF', 'INACTIF'])],
            'quartiers'   => ['nullable', 'string', 'max:2000'],
        ];
    }

    // Transforme "Analakely, Behoririka" en ['Analakely', 'Behoririka']
    public function quartiersListe(): array
    {
        return collect(preg_split('/[\r\n,;]+/', (string) $this->input('quartiers')))
            ->map(fn ($nom) => trim($nom))
            ->filter()
            ->unique(fn ($nom) => mb_strtolower($nom))
            ->values()
            ->all();
    }

    // Un quartier ne peut appartenir qu'à une seule zone
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $id = $this->route('zone')?->id;

            $pris = Quartier::with('zone')
                ->whereIn('nom', $this->quartiersListe())
                ->when($id, fn ($q) => $q->where('zone_id', '!=', $id))
                ->get();

            foreach ($pris as $quartier) {
                $v->errors()->add('quartiers', "Le quartier « {$quartier->nom} » appartient déjà à {$quartier->zone->nom}.");
            }
        });
    }
}