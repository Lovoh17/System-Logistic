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
            $table->string('tipo', 30)->default('externo'); 

            $table->string('vehiculo_tipo', 50)->nullable();  
            $table->string('vehiculo_placa', 20)->nullable();
            $table->string('vehiculo_modelo', 80)->nullable();
            $table->decimal('capacidad_kg', 10, 2)->nullable();
            $table->decimal('capacidad_m3', 10, 2)->nullable();
            $table->boolean('tiene_refrigeracion')->default(false);
            $table->boolean('tiene_gps')->default(false);

            // Datos del conductor
            $table->string('conductor_nombre', 100)->nullable();
            $table->string('conductor_licencia', 30)->nullable();
            $table->string('conductor_telefono', 20)->nullable();

            // Contacto empresa transportista
            $table->string('email', 150)->nullable();
            $table->string('telefono', 20)->nullable();

            // Tarifas
            $table->decimal('tarifa_km', 8, 2)->nullable();
            $table->decimal('tarifa_fija', 10, 2)->nullable();

            $table->enum('estado', ['disponible', 'en_ruta', 'mantenimiento', 'inactivo'])->default('disponible');

            // Ubicación GPS en tiempo real
            $table->decimal('latitud', 10, 8)->nullable();
            $table->decimal('longitud', 11, 8)->nullable();
            $table->string('ubicacion_actual', 255)->nullable();
            $table->timestamp('ultima_ubicacion_at')->nullable();

            $table->text('notas')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transportistas');
    }
};
