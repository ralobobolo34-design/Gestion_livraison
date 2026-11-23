<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function client(): HasOne
    {
        return $this->hasOne(Client::class);
    }

    public function livreur(): HasOne
    {
        return $this->hasOne(Livreur::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isStaff(): bool
    {
        return in_array($this->role, ['admin', 'agent'], true);
    }

    public function isLivreur(): bool
    {
        return $this->role === 'livreur';
    }

    public function isClient(): bool
    {
        return $this->role === 'client';
    }

    // Page d'accueil après connexion, selon le rôle
    public function homeUrl(): string
    {
        return match ($this->role) {
            'admin', 'agent' => route('dashboard'),
            'livreur'        => url('/livreur/tournees'),
            default          => route('espace.client'),
        };
    }
}