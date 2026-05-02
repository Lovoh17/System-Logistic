<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categorias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100); 
            $table->string('slug', 120)->unique();
            $table->text('descripcion')->nullable();
            $table->string('icono', 50)->nullable();
            $table->string('color', 20)->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('categorias')->nullOnDelete();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 30)->unique();
            $table->string('sku', 50)->unique()->nullable();
            $table->string('nombre', 150);
            $table->text('descripcion')->nullable();
            $table->foreignId('categoria_id')->nullable()->constrained('categorias')->nullOnDelete();
            $table->foreignId('proveedor_id')->nullable()->constrained('proveedores')->nullOnDelete();
            $table->string('unidad_medida', 20)->default('unidad'); // unidad, kg, litro, caja, palet
            $table->decimal('precio_compra', 12, 2)->default(0.00);
            $table->decimal('precio_venta', 12, 2)->default(0.00);
            $table->decimal('stock_actual', 12, 3)->default(0.000);
            $table->decimal('stock_minimo', 12, 3)->default(0.000);
            $table->decimal('stock_maximo', 12, 3)->default(0.000);
            $table->decimal('peso_kg', 8, 3)->nullable();
            $table->string('ubicacion_almacen', 50)->nullable(); // Ej: A-01-02
            $table->string('imagen')->nullable();
            $table->boolean('requiere_refrigeracion')->default(false);
            $table->boolean('es_perecedero')->default(false);
            $table->integer('vida_util_dias')->nullable();
            $table->enum('estado', ['activo', 'inactivo', 'descontinuado'])->default('activo');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productos');
        Schema::dropIfExists('categorias');
    }
};
