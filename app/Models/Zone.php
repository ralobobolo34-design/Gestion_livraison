<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

class Zone extends Model
{
    protected $fillable = ['nom', 'description', 'tarif_base', 'statut'];

    public function tarifs(): HasMany
    {
        return $this->hasMany(Tarif::class);
    }

    public function commandes(): HasMany
    {
        return $this->hasMany(Commande::class);
    }

    public function quartiers(): HasMany
    {
        return $this->hasMany(Quartier::class);
    }

    // Aligne les quartiers de la zone sur la liste donnée (ajoute les nouveaux, retire les absents)
    public function synchroniserQuartiers(array $noms): void
    {
        $existants = $this->quartiers()->get()->keyBy(fn ($q) => mb_strtolower($q->nom));
        $voulus    = collect($noms)->keyBy(fn ($n) => mb_strtolower($n));

        foreach ($existants->diffKeys($voulus) as $quartier) {
            if ($quartier->commandes()->exists()) {
                throw ValidationException::withMessages([
                    'quartiers' => "Le quartier « {$quartier->nom} » est utilisé par des commandes : il ne peut pas être retiré.",
                ]);
            }
            $quartier->delete();
        }

        foreach ($voulus->diffKeys($existants) as $nom) {
            $this->quartiers()->create(['nom' => $nom]);
        }
    }
}