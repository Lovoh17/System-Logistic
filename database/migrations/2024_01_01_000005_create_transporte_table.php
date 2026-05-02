<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transportistas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique();
            $table->string('nombre', 150);
            $table->string('tipo', 30)->default('externo'); // propio, externo
            $table->string('vehiculo_tipo', 50)->nullable(); // camion, pickup, furgon, moto
            $table->string('vehiculo_placa', 20)->nullable();
            $table->string('vehiculo_modelo', 80)->nullable();
            $table->decimal('capacidad_kg', 10, 2)->nullable();
            $table->decimal('capacidad_m3', 10, 2)->nullable();
            $table->string('conductor_nombre', 100)->nullable();
            $table->string('conductor_licencia', 30)->nullable();
            $table->string('conductor_telefono', 20)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->boolean('tiene_refrigeracion')->default(false);
            $table->boolean('tiene_gps')->default(false);
            $table->decimal('tarifa_km', 8, 2)->nullable();
            $table->decimal('tarifa_fija', 10, 2)->nullable();
            $table->enum('estado', ['disponible', 'en_ruta', 'mantenimiento', 'inactivo'])->default('disponible');
            $table->text('notas')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('envios', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 25)->unique();
            $table->foreignId('pedido_venta_id')->constrained('pedidos_venta');
            $table->foreignId('transportista_id')->nullable()->constrained('transportistas')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->date('fecha_programada');
            $table->dateTime('fecha_salida')->nullable();
            $table->dateTime('fecha_entrega_estimada')->nullable();
            $table->dateTime('fecha_entrega_real')->nullable();
            $table->enum('estado', [
                'programado', 'en_preparacion', 'despachado', 'en_transito', 'en_destino', 'entregado', 'fallido', 'devuelto'
            ])->default('programado');
            $table->string('origen_nombre', 150)->nullable();
            $table->text('origen_direccion')->nullable();
            $table->string('destino_nombre', 150)->nullable();
            $table->text('destino_direccion')->nullable();
            $table->string('destino_departamento', 80)->nullable();
            $table->string('destino_municipio', 80)->nullable();
            $table->decimal('distancia_km', 8, 2)->nullable();
            $table->decimal('peso_total_kg', 10, 3)->nullable();
            $table->decimal('volumen_total_m3', 10, 3)->nullable();
            $table->decimal('costo_envio', 12, 2)->default(0.00);
            $table->string('numero_seguimiento', 50)->nullable();
            $table->decimal('latitud_actual', 10, 8)->nullable();
            $table->decimal('longitud_actual', 11, 8)->nullable();
            $table->string('firma_receptor', 150)->nullable();
            $table->string('foto_entrega')->nullable();
            $table->text('observaciones')->nullable();
            $table->text('motivo_fallo')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('seguimiento_envios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('envio_id')->constrained('envios')->cascadeOnDelete();
            $table->string('evento', 100);
            $table->text('descripcion')->nullable();
            $table->string('ubicacion', 150)->nullable();
            $table->decimal('latitud', 10, 8)->nullable();
            $table->decimal('longitud', 11, 8)->nullable();
            $table->string('responsable', 100)->nullable();
            $table->dateTime('fecha_hora');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seguimiento_envios');
        Schema::dropIfExists('envios');
        Schema::dropIfExists('transportistas');
    }
};
