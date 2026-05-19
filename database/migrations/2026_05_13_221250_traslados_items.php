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
        Schema::create('traslados_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('traslado_id')
                  ->constrained('traslados')
                  ->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos');

            $table->decimal('cantidad_sugerida', 12, 3);
            $table->decimal('cantidad_real', 12, 3)->nullable();
            $table->string('lote', 50)->nullable();
            $table->date('fecha_vencimiento')->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();

            $table->index('traslado_id');
            $table->unique(['traslado_id', 'producto_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('traslados_items');
    }
};
