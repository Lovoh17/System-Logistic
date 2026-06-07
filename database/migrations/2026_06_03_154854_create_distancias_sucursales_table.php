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
        Schema::create('distancias_sucursales', function (Blueprint $table) {
            $table->id();
            // Siempre almacen1_id < almacen2_id para evitar duplicados
            $table->foreignId('almacen1_id')->constrained('almacenes')->cascadeOnDelete();
            $table->foreignId('almacen2_id')->constrained('almacenes')->cascadeOnDelete();
            $table->decimal('distancia_km', 8, 2);
            $table->timestamps();

            $table->unique(['almacen1_id', 'almacen2_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('distancias_sucursales');
    }
};
