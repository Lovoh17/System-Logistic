<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cuentas_contables', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique();
            $table->string('nombre', 150);
            $table->enum('tipo', ['activo', 'pasivo', 'capital', 'ingreso', 'costo', 'gasto']);
            $table->enum('naturaleza', ['deudora', 'acreedora']);
            $table->tinyInteger('nivel')->default(1)->comment('1=grupo 2=subgrupo 3=cuenta 4=subcuenta');
            $table->foreignId('cuenta_padre_id')->nullable()->constrained('cuentas_contables')->nullOnDelete();
            $table->boolean('acepta_movimientos')->default(true)->comment('false para cuentas de encabezado');
            $table->boolean('activa')->default(true);
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cuentas_contables');
    }
};
