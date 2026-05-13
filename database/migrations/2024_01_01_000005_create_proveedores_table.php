<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proveedores', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique();
            $table->string('nombre', 150);
            $table->string('razon_social', 200)->nullable();
            $table->string('ruc', 20)->unique()->nullable();
            $table->string('nit', 20)->unique()->nullable();

            // Contacto principal de la empresa
            $table->string('email')->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('celular', 20)->nullable();

            // Persona de contacto (contacto comercial)
            $table->string('contacto_nombre', 100)->nullable();
            $table->string('contacto_email')->nullable();
            $table->string('contacto_telefono', 20)->nullable();

            // Ubicación
            $table->string('pais', 80)->default('El Salvador');
            $table->string('departamento', 80)->nullable();
            $table->string('municipio', 80)->nullable();
            $table->text('direccion')->nullable();

            // Condiciones comerciales
            $table->string('categoria', 30)->default('general'); // general | materia_prima | servicios
            $table->unsignedSmallInteger('tiempo_entrega_dias')->default(3);
            $table->decimal('calificacion', 3, 2)->default(0.00);

            $table->enum('estado', ['activo', 'inactivo', 'suspendido'])->default('activo');
            $table->text('notas')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proveedores');
    }
};
