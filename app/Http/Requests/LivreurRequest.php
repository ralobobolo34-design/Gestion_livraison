<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LivreurRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // les rôles seront gérés plus tard
    }

    public function rules(): array
    {
        $id = $this->route('livreur')?->id;

        return [
            'nom'             => ['required', 'string', 'max:100'],
            'prenom'          => ['required', 'string', 'max:100'],
            'telephone'       => ['required', 'string', 'max:20'],
            'email'           => ['nullable', 'email', 'max:150',
                                  Rule::unique('livreurs', 'email')->ignore($id)],
            'type_vehicule'   => ['required', Rule::in(['MOTO', 'VOITURE', 'CAMION', 'AUTRE'])],
            'numero_vehicule' => ['nullable', 'string', 'max:30'],
            'statut'          => ['required', Rule::in(['ACTIF', 'INACTIF', 'INDISPONIBLE'])],
        ];
    }
}