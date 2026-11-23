<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // les rôles seront gérés plus tard
    }

    public function rules(): array
    {
        $id = $this->route('client')?->id;

        return [
            'nom'       => ['required', 'string', 'max:100'],
            'prenom'    => ['required', 'string', 'max:100'],
            // RM01 : au moins un contact valide
            'telephone' => ['nullable', 'string', 'max:20', 'required_without:email'],
            'email'     => ['nullable', 'email', 'max:150', 'required_without:telephone',
                            Rule::unique('clients', 'email')->ignore($id)],
            'adresse'   => ['nullable', 'string', 'max:255'],
            'statut'    => ['sometimes', Rule::in(['ACTIF', 'INACTIF'])],
        ];
    }

    public function messages(): array
    {
        return [
            'telephone.required_without' => 'Renseignez au moins un téléphone ou un e-mail.',
            'email.required_without'     => 'Renseignez au moins un téléphone ou un e-mail.',
        ];
    }
}