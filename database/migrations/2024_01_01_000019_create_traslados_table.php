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

            $table->foreignId('almacen_origen_id')->constrained('almacenes');
            $table->foreignId('almacen_destino_id')->constrained('almacenes');

            $table->enum('estado', ['sugerido', 'aprobado', 'completado', 'cancelado'])->default('sugerido');
            $table->text('motivo')->nullable();

            // Auditoría
            $table->foreignId('creado_por')->constrained('users');
            $table->foreignId('aprobado_por')->nullable()->constrained('users');
            $table->dateTime('fecha_aprobacion')->nullable();
            $table->dateTime('fecha_completado')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['estado', 'created_at']);
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('traslados');
    }
};
