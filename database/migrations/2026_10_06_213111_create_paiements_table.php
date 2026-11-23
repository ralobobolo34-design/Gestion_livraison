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
       Schema::create('paiements', function (Blueprint $table) {
    $table->id();
    $table->string('reference')->unique();
    $table->foreignId('commande_id')->constrained()->cascadeOnDelete();
    $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
    $table->unsignedInteger('montant');
    $table->string('mode');                     // ESPECES, MOBILE_MONEY, CARTE, PAIEMENT_LIVRAISON
    $table->dateTime('date_paiement');
    $table->string('statut')->default('EN_ATTENTE');
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paiements');
    }
};
