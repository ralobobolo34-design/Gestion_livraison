<?php

namespace App\Http\Requests;

use App\Models\Tarif;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class TarifRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'zone_id'        => ['required', 'exists:zones,id'],
            'poids_min'      => ['required', 'numeric', 'min:0'],
            'poids_max'      => ['required', 'numeric', 'gt:poids_min'],
            'type_livraison' => ['required', Rule::in(['STANDARD', 'EXPRESS'])],
            'priorite'       => ['required', Rule::in(['NORMALE', 'URGENTE'])],
            'prix'           => ['required', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'poids_max.gt' => 'Le poids maximum doit être supérieur au poids minimum.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            if ($v->errors()->isNotEmpty()) {
                return;
            }

            // Deux tranches de la même zone, du même type et de la même priorité ne doivent pas se chevaucher
            $chevauche = Tarif::where('zone_id', $this->zone_id)
                ->where('type_livraison', $this->type_livraison)
                ->where('priorite', $this->priorite)
                ->where('poids_min', '<', $this->poids_max)
                ->where('poids_max', '>', $this->poids_min)
                ->when($this->route('tarif'), fn ($q, $t) => $q->where('id', '!=', $t->id))
                ->exists();

            if ($chevauche) {
                $v->errors()->add('poids_min', 'Cette tranche de poids chevauche un tarif existant pour cette zone, ce type et cette priorité.');
            }
        });
    }
}