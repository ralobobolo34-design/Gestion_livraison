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
       Schema::create('tournee_details', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tournee_id')->constrained()->cascadeOnDelete();
    $table->foreignId('colis_id')->constrained()->cascadeOnDelete();
    $table->unsignedSmallInteger('ordre')->nullable();
    $table->timestamps();

    $table->unique(['tournee_id', 'colis_id']);
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tournee_details');
    }
};
