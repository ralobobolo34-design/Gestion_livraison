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
        Schema::create('colis', function (Blueprint $table) {
    $table->id();
    $table->string('reference')->unique();
    $table->foreignId('commande_id')->constrained()->cascadeOnDelete();
    $table->string('description')->nullable();
    $table->decimal('poids', 8, 2);
    $table->string('dimensions')->nullable();
    $table->unsignedInteger('valeur_declaree')->nullable();
    $table->boolean('fragile')->default(false);
    $table->unsignedInteger('prix')->default(0);
    $table->string('statut')->default('CREE');
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('colis');
    }
};
