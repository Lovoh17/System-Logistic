<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pedidos_venta_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_venta_id')
                  ->constrained('pedidos_venta')
                  ->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos');

            $table->decimal('cantidad', 12, 3);
            $table->decimal('cantidad_despachada', 12, 3)->default(0.000);
            $table->decimal('precio_unitario', 12, 2);
            $table->decimal('descuento', 5, 2)->default(0.00); // porcentaje por ítem
            $table->decimal('subtotal', 12, 2);
            $table->string('unidad_medida', 20)->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();

            $table->index('pedido_venta_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedidos_venta_items');
    }
};
