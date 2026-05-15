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
        Schema::create('direcciones_clientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')
                  ->constrained('clientes')
                  ->cascadeOnDelete();

            $table->string('alias', 60)->nullable();
            $table->string('destinatario', 150)->nullable();
            $table->string('telefono', 20)->nullable();

            $table->string('pais', 80)->default('El Salvador');
            $table->string('departamento', 80)->nullable();
            $table->string('municipio', 80)->nullable();
            $table->text('direccion');
            $table->string('referencia', 255)->nullable();

            $table->boolean('es_principal')->default(false);
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['cliente_id', 'es_principal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('direcciones_clientes');
    }
};
