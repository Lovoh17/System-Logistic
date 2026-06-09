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
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('almacen_id')->constrained('almacenes')->cascadeOnDelete();
            $table->string('codigo', 20)->unique();

            $table->string('vehiculo_tipo', 50)->nullable();
            $table->string('vehiculo_placa', 20)->nullable();
            $table->string('vehiculo_modelo', 80)->nullable();
            $table->decimal('capacidad_kg', 10, 2)->nullable();
            $table->decimal('capacidad_m3', 10, 2)->nullable();
            $table->boolean('tiene_refrigeracion')->default(false);
            $table->boolean('tiene_gps')->default(false);

            $table->enum('estado', ['disponible', 'en_ruta', 'mantenimiento', 'inactivo'])->default('disponible');

            $table->decimal('latitud', 10, 8)->nullable();
            $table->decimal('longitud', 11, 8)->nullable();
            $table->string('ubicacion_actual', 255)->nullable();
            $table->timestamp('ultima_ubicacion_at')->nullable();

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
