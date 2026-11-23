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
     Schema::create('commandes', function (Blueprint $table) {
    $table->id();
    $table->string('reference')->unique();
    $table->foreignId('client_id')->constrained()->restrictOnDelete();
    $table->foreignId('zone_id')->constrained()->restrictOnDelete();
    $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // agent
    $table->string('adresse_depart');
    $table->string('adresse_destination');
    $table->string('type_livraison')->default('STANDARD');
    $table->string('priorite')->default('NORMALE');
    $table->unsignedInteger('montant')->default(0);
    $table->date('date_commande');
    $table->date('date_livraison_prevue')->nullable();
    $table->string('statut')->default('EN_ATTENTE');
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commandes');
    }
};
