<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('traslados', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 20)->unique();
            $table->foreignId('producto_id')->constrained('productos');
            $table->foreignId('almacen_origen_id')->constrained('almacenes');
            $table->foreignId('almacen_destino_id')->constrained('almacenes');

            $table->decimal('cantidad_sugerida', 12, 3);
            $table->decimal('cantidad_real', 12, 3)->nullable(); // se completa al aprobar/ejecutar

            $table->enum('estado', ['sugerido', 'aprobado', 'completado', 'cancelado'])->default('sugerido');
            $table->text('motivo')->nullable();

            // Auditoría de aprobación y ejecución
            $table->foreignId('creado_por')->constrained('users');
            $table->foreignId('aprobado_por')->nullable()->constrained('users');
            $table->dateTime('fecha_aprobacion')->nullable();
            $table->dateTime('fecha_completado')->nullable();

            $table->timestamps();

            $table->index(['estado', 'created_at']);
            $table->index('producto_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('traslados');
    }
};
