<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('almacenes', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique();
            $table->string('nombre', 100);
            $table->text('direccion')->nullable();
            $table->string('responsable', 100)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->boolean('es_principal')->default(false);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('movimientos_inventario', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 25)->unique();
            $table->foreignId('producto_id')->constrained('productos');
            $table->foreignId('almacen_id')->nullable()->constrained('almacenes')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->enum('tipo', [
                'entrada_compra',      // recepcion de pedido de compra
                'salida_venta',        // despacho de pedido de venta
                'ajuste_positivo',     // ajuste de inventario (+)
                'ajuste_negativo',     // ajuste de inventario (-)
                'traslado_entrada',    // traslado entre almacenes (destino)
                'traslado_salida',     // traslado entre almacenes (origen)
                'devolucion_compra',   // devolucion a proveedor
                'devolucion_venta',    // devolucion de cliente
                'merma',               // perdida/dano
                'inventario_inicial'   // carga inicial
            ]);
            $table->decimal('cantidad', 12, 3);
            $table->decimal('stock_anterior', 12, 3);
            $table->decimal('stock_nuevo', 12, 3);
            $table->decimal('costo_unitario', 12, 4)->nullable();
            $table->decimal('costo_total', 12, 2)->nullable();
            $table->morphs('referencia'); // pedido_compra, pedido_venta, etc.
            $table->string('lote', 50)->nullable();
            $table->date('fecha_vencimiento')->nullable();
            $table->text('motivo')->nullable();
            $table->dateTime('fecha_movimiento');
            $table->timestamps();

            $table->index(['producto_id', 'fecha_movimiento']);
            $table->index(['tipo', 'fecha_movimiento']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos_inventario');
        Schema::dropIfExists('almacenes');
    }
};
