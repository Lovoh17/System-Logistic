<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 30)->unique();
            $table->string('sku', 50)->unique()->nullable();
            $table->string('nombre', 150);
            $table->text('descripcion')->nullable();

            // Clasificación
            $table->foreignId('categoria_id')
                  ->nullable()
                  ->constrained('categorias')
                  ->nullOnDelete();
            $table->foreignId('proveedor_id')
                  ->nullable()
                  ->constrained('proveedores')
                  ->nullOnDelete();

            // Unidad y precios
            $table->string('unidad_medida', 20)->default('unidad'); // unidad|kg|litro|caja|palet
            $table->decimal('precio_compra', 12, 2)->default(0.00);
            $table->decimal('precio_venta', 12, 2)->default(0.00);

            // Stock global (consolidado — por sucursal va en inventario_almacen)
            $table->decimal('stock_actual', 12, 3)->default(0.000);
            $table->decimal('stock_minimo', 12, 3)->default(0.000);
            $table->decimal('stock_maximo', 12, 3)->default(0.000);

            // Atributos físicos
            $table->decimal('peso_kg', 8, 3)->nullable();
            $table->string('ubicacion_almacen', 50)->nullable(); // Ej: A-01-02
            $table->string('imagen')->nullable();

            // Atributos especiales
            $table->boolean('requiere_refrigeracion')->default(false);
            $table->boolean('es_perecedero')->default(false);
            $table->unsignedSmallInteger('vida_util_dias')->nullable();

            $table->enum('estado', ['activo', 'inactivo', 'descontinuado'])->default('activo');
            $table->timestamps();
            $table->softDeletes();

            $table->index('estado');
            $table->index(['categoria_id', 'estado']);
            $table->index('stock_actual');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
