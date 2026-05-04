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
            $table->foreignId('producto_id')->constrained()->onDelete('cascade');
            $table->foreignId('almacen_origen_id')->constrained('almacenes')->onDelete('cascade');
            $table->foreignId('almacen_destino_id')->constrained('almacenes')->onDelete('cascade');
            $table->foreignId('transportista_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('cantidad', 12, 3);
            $table->decimal('cantidad_recibida', 12, 3)->default(0);
            $table->enum('estado', [
                'pendiente', 'asignado', 'en_transito', 'entregado', 'cancelado'
            ])->default('pendiente');
            $table->text('motivo')->nullable();
            $table->date('fecha_programada')->nullable();
            $table->date('fecha_salida')->nullable();
            $table->date('fecha_entrega_estimada')->nullable();
            $table->date('fecha_entrega_real')->nullable();
            $table->text('observaciones')->nullable();
            $table->foreignId('creado_por')->constrained('users');
            $table->foreignId('asignado_por')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['estado', 'fecha_programada']);
            $table->index('almacen_origen_id');
            $table->index('almacen_destino_id');
        });
        
        // Crear tabla de seguimiento de traslados
        Schema::create('seguimiento_traslados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('traslado_id')->constrained()->onDelete('cascade');
            $table->string('evento', 100);
            $table->text('descripcion')->nullable();
            $table->string('ubicacion', 150)->nullable();
            $table->foreignId('user_id')->constrained();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seguimiento_traslados');
        Schema::dropIfExists('traslados');
    }
};