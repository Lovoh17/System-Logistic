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

            $table->foreignId('categoria_id')
                  ->nullable()
                  ->constrained('categorias')
                  ->nullOnDelete();
            $table->foreignId('proveedor_id')
                  ->nullable()
                  ->constrained('proveedores')
                  ->nullOnDelete();

            $table->string('unidad_medida', 20)->default('unidad'); 
            $table->decimal('precio_compra', 12, 2)->default(0.00);
            $table->decimal('precio_venta', 12, 2)->default(0.00);

            
            $table->decimal('peso_kg', 8, 3)->nullable();
            $table->string('ubicacion_almacen', 50)->nullable(); 
            $table->string('imagen')->nullable();

            $table->boolean('requiere_refrigeracion')->default(false);
            $table->boolean('es_perecedero')->default(false);
            $table->unsignedSmallInteger('vida_util_dias')->nullable();

            $table->enum('estado', ['activo', 'inactivo', 'descontinuado'])->default('activo');
            $table->timestamps();
            $table->softDeletes();

            $table->index('estado');
            $table->index(['categoria_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
