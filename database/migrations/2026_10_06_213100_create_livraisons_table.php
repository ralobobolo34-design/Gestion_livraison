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
        Schema::create('livraisons', function (Blueprint $table) {
    $table->id();
    $table->foreignId('colis_id')->constrained()->cascadeOnDelete();
    $table->foreignId('livreur_id')->nullable()->constrained()->nullOnDelete();
    $table->dateTime('date_livraison')->nullable();
    $table->string('nom_receptionnaire')->nullable();
    $table->text('commentaire')->nullable();
    $table->string('motif_echec')->nullable();
    $table->string('signature')->nullable();
    $table->string('photo')->nullable();
    $table->decimal('latitude', 10, 7)->nullable();
    $table->decimal('longitude', 10, 7)->nullable();
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('livraisons');
    }
};
