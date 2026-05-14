<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventario_almacen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')
                  ->constrained('productos')
                  ->cascadeOnDelete();
            $table->foreignId('almacen_id')
                  ->constrained('almacenes')
                  ->cascadeOnDelete();

            $table->decimal('stock_actual', 12, 3)->default(0.000);
            $table->decimal('stock_minimo', 12, 3)->default(0.000);
            $table->decimal('stock_maximo', 12, 3)->default(0.000);
            $table->decimal('punto_reorden', 12, 3)->default(0.000);

            $table->timestamps();

            $table->unique(['producto_id', 'almacen_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventario_almacen');
    }
};
