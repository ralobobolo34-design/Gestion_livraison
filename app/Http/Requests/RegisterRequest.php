<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['email' => mb_strtolower(trim((string) $this->input('email')))]);
    }

    public function rules(): array
    {
        return [
            'nom'       => ['required', 'string', 'max:100'],
            'prenom'    => ['required', 'string', 'max:100'],
            'telephone' => ['required', 'string', 'max:20', 'regex:/^[0-9 +().\-]{7,20}$/'],
            // L'adresse e-mail ne doit appartenir ni à un compte ni à une fiche client existante
            'email'     => ['required', 'email', 'max:150', 'unique:users,email', 'unique:clients,email'],
            'adresse'   => ['nullable', 'string', 'max:255'],
            'password'  => ['required', 'confirmed', Password::min(8)],
            // Champ piège invisible : un robot le remplit, pas un humain
            'website'   => ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'telephone.regex'   => 'Numéro de téléphone invalide.',
            'email.unique'      => 'Cette adresse e-mail est déjà utilisée. Connectez-vous, ou contactez-nous si vous êtes déjà client.',
            'password.confirmed' => 'Les deux mots de passe ne correspondent pas.',
            'website.prohibited' => 'Requête refusée.',
        ];
    }
}