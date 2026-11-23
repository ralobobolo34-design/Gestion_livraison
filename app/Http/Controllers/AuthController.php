<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'E-mail ou mot de passe incorrect.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        $user = Auth::user();

        // L'adresse mémorisée avant la connexion n'est suivie que si ce rôle y a droit
        $intended = $request->session()->pull('url.intended');

        return redirect($this->adresseAutorisee($intended, $user) ? $intended : $user->homeUrl());
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(RegisterRequest $request)
    {
        $data = $request->validated();

        // Le rôle est imposé ici : il ne vient jamais du formulaire
        $user = DB::transaction(function () use ($data) {
            $user = User::create([
                'name'     => $data['prenom'] . ' ' . $data['nom'],
                'email'    => $data['email'],
                'password' => $data['password'],
                'role'     => 'client',
            ]);

            Client::create([
                'user_id'   => $user->id,
                'nom'       => $data['nom'],
                'prenom'    => $data['prenom'],
                'telephone' => $data['telephone'],
                'email'     => $data['email'],
                'adresse'   => $data['adresse'] ?? null,
                'statut'    => 'ACTIF',
            ]);

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('espace.client')
            ->with('success', 'Bienvenue ! Votre compte est créé : vous pouvez passer votre première commande.');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('accueil');
    }

    // Vérifie qu'une adresse existe sur ce site et que le rôle de l'utilisateur y a accès
    private function adresseAutorisee(?string $url, User $user): bool
    {
        if (! $url || ! str_starts_with($url, url('/'))) {
            return false;
        }

        try {
            $route = app('router')->getRoutes()->match(Request::create($url, 'GET'));
        } catch (\Throwable $e) {
            return false;   // adresse inconnue
        }

        if ($route->isFallback) {
            return false;
        }

        foreach ($route->gatherMiddleware() as $middleware) {
            if (is_string($middleware) && str_starts_with($middleware, 'role:')) {
                $roles = explode(',', substr($middleware, strlen('role:')));

                if (! in_array($user->role, $roles, true)) {
                    return false;
                }
            }
        }

        return true;
    }
}