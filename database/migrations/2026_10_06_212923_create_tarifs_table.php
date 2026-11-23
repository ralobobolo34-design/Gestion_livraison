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
        Schema::create('tarifs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('zone_id')->constrained()->cascadeOnDelete();
    $table->decimal('poids_min', 8, 2);
    $table->decimal('poids_max', 8, 2);
    $table->string('type_livraison')->default('STANDARD');
    $table->string('priorite')->default('NORMALE');
    $table->unsignedInteger('prix');
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tarifs');
    }
};
