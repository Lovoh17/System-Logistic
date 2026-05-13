<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimientos_inventario', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 25)->unique();
            $table->foreignId('producto_id')->constrained('productos');
            $table->foreignId('almacen_id')
                  ->nullable()
                  ->constrained('almacenes')
                  ->nullOnDelete();
            $table->foreignId('user_id')->constrained('users');

            $table->enum('tipo', [
                'entrada_compra',    // recepción de OC
                'salida_venta',      // despacho de OV
                'ajuste_positivo',   // ajuste inventario (+)
                'ajuste_negativo',   // ajuste inventario (-)
                'traslado_entrada',  // recepción de traslado
                'traslado_salida',   // salida por traslado
                'devolucion_compra', // devolución a proveedor
                'devolucion_venta',  // devolución de cliente
                'merma',             // pérdida / daño
                'inventario_inicial',// carga inicial
            ]);

            $table->decimal('cantidad', 12, 3);
            $table->decimal('stock_anterior', 12, 3);
            $table->decimal('stock_nuevo', 12, 3);
            $table->decimal('costo_unitario', 12, 4)->nullable();
            $table->decimal('costo_total', 12, 2)->nullable();

            // Referencia polimórfica nullable (ajustes manuales no tienen referencia)
            $table->nullableMorphs('referencia');

            $table->string('lote', 50)->nullable();
            $table->date('fecha_vencimiento')->nullable();
            $table->text('motivo')->nullable();
            $table->dateTime('fecha_movimiento');
            $table->timestamps();

            // Índices para consultas de kardex y reportes
            $table->index(['producto_id', 'fecha_movimiento']);
            $table->index(['tipo', 'fecha_movimiento']);
            $table->index(['almacen_id', 'producto_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos_inventario');
    }
};
