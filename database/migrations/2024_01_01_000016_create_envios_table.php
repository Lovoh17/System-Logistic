<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('envios', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 25)->unique();
            $table->foreignId('pedido_venta_id')->constrained('pedidos_venta');
            $table->foreignId('transportista_id')
                  ->nullable()
                  ->constrained('transportistas')
                  ->nullOnDelete();
            $table->foreignId('user_id')->constrained('users'); // despachador

            // Fechas del ciclo de vida
            $table->date('fecha_programada');
            $table->dateTime('fecha_salida')->nullable();
            $table->dateTime('fecha_entrega_estimada')->nullable();
            $table->dateTime('fecha_entrega_real')->nullable();

            $table->enum('estado', [
                'programado', 'en_preparacion', 'despachado',
                'en_transito', 'en_destino', 'entregado', 'fallido', 'devuelto',
            ])->default('programado');

            // Origen
            $table->string('origen_nombre', 150)->nullable();
            $table->text('origen_direccion')->nullable();

            // Destino
            $table->string('destino_nombre', 150)->nullable();
            $table->text('destino_direccion')->nullable();
            $table->string('destino_departamento', 80)->nullable();
            $table->string('destino_municipio', 80)->nullable();

            // Métricas de carga y ruta
            $table->decimal('distancia_km', 8, 2)->nullable();
            $table->decimal('peso_total_kg', 10, 3)->nullable();
            $table->decimal('volumen_total_m3', 10, 3)->nullable();
            $table->decimal('costo_envio', 12, 2)->default(0.00);

            // Seguimiento
            $table->string('numero_seguimiento', 50)->nullable();
            $table->decimal('latitud_actual', 10, 8)->nullable();
            $table->decimal('longitud_actual', 11, 8)->nullable();

            // Evidencia de entrega
            $table->string('firma_receptor', 150)->nullable();
            $table->string('foto_entrega')->nullable();

            $table->text('observaciones')->nullable();
            $table->text('motivo_fallo')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['estado', 'fecha_programada']);
            $table->index('transportista_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('envios');
    }
};
