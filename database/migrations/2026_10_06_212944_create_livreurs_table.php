<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
       Schema::create('livreurs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->nullable()->unique()->constrained()->nullOnDelete();
    $table->string('nom');
    $table->string('prenom');
    $table->string('telephone');
    $table->string('email')->nullable();
    $table->string('type_vehicule');          // MOTO, VOITURE, CAMION, AUTRE
    $table->string('numero_vehicule')->nullable();
    $table->string('statut')->default('ACTIF'); // ACTIF, INACTIF, INDISPONIBLE
    $table->date('date_inscription')->nullable();
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('livreurs');
    }
};
