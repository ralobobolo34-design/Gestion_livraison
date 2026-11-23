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
    Schema::table('commandes', function (Blueprint $table) {
        $table->foreignId('quartier_id')->nullable()->after('zone_id')
              ->constrained('quartiers')->restrictOnDelete();
    });
}

public function down(): void
{
    Schema::table('commandes', function (Blueprint $table) {
        $table->dropConstrainedForeignId('quartier_id');
    });
}
};
