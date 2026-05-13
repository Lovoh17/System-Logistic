<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique();
            $table->string('nombre', 150);
            $table->string('razon_social', 200)->nullable();

            // Identificación fiscal El Salvador
            $table->string('nit', 20)->nullable();
            $table->string('dui', 15)->nullable();

            // Contacto
            $table->string('email')->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('celular', 20)->nullable();

            // Ubicación
            $table->string('pais', 80)->default('El Salvador');
            $table->string('departamento', 80)->nullable();
            $table->string('municipio', 80)->nullable();
            $table->text('direccion_principal')->nullable();

            // Condiciones comerciales
            $table->string('tipo', 30)->default('minorista'); // minorista | mayorista | corporativo
            $table->decimal('limite_credito', 12, 2)->default(0.00);
            $table->unsignedSmallInteger('dias_credito')->default(0);

            $table->enum('estado', ['activo', 'inactivo', 'bloqueado'])->default('activo');
            $table->text('notas')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('estado');
            $table->index('tipo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
