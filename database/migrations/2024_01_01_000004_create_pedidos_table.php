<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pedidos de Compra (a proveedores)
        Schema::create('pedidos_compra', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 20)->unique();
            $table->foreignId('proveedor_id')->constrained('proveedores');
            $table->foreignId('user_id')->constrained('users'); // quien creó el pedido
            $table->date('fecha_pedido');
            $table->date('fecha_requerida')->nullable();
            $table->date('fecha_recepcion')->nullable();
            $table->enum('estado', [
                'borrador', 'enviado', 'confirmado', 'parcial', 'recibido', 'cancelado'
            ])->default('borrador');
            $table->decimal('subtotal', 12, 2)->default(0.00);
            $table->decimal('impuesto', 12, 2)->default(0.00);
            $table->decimal('descuento', 12, 2)->default(0.00);
            $table->decimal('total', 12, 2)->default(0.00);
            $table->string('moneda', 3)->default('USD');
            $table->text('condiciones_pago')->nullable();
            $table->text('notas')->nullable();
            $table->text('motivo_cancelacion')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('pedidos_compra_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_compra_id')->constrained('pedidos_compra')->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos');
            $table->decimal('cantidad', 12, 3);
            $table->decimal('cantidad_recibida', 12, 3)->default(0.000);
            $table->decimal('precio_unitario', 12, 2);
            $table->decimal('descuento', 5, 2)->default(0.00);
            $table->decimal('subtotal', 12, 2);
            $table->string('unidad_medida', 20)->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();
        });

        // Pedidos de Venta (de clientes)
        Schema::create('pedidos_venta', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 20)->unique();
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->foreignId('user_id')->constrained('users');
            $table->date('fecha_pedido');
            $table->date('fecha_requerida')->nullable();
            $table->date('fecha_entrega_real')->nullable();
            $table->enum('estado', [
                'borrador', 'confirmado', 'en_preparacion', 'listo', 'en_transito', 'entregado', 'cancelado', 'devolucion'
            ])->default('borrador');
            $table->enum('prioridad', ['baja', 'normal', 'alta', 'urgente'])->default('normal');
            $table->decimal('subtotal', 12, 2)->default(0.00);
            $table->decimal('impuesto', 12, 2)->default(0.00);
            $table->decimal('descuento', 12, 2)->default(0.00);
            $table->decimal('costo_envio', 12, 2)->default(0.00);
            $table->decimal('total', 12, 2)->default(0.00);
            $table->string('moneda', 3)->default('USD');
            $table->text('direccion_entrega')->nullable();
            $table->string('departamento_entrega', 80)->nullable();
            $table->string('municipio_entrega', 80)->nullable();
            $table->text('instrucciones_entrega')->nullable();
            $table->string('canal_venta', 30)->default('directo'); // directo, telefono, web, distribuidor
            $table->text('notas')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('pedidos_venta_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_venta_id')->constrained('pedidos_venta')->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos');
            $table->decimal('cantidad', 12, 3);
            $table->decimal('cantidad_despachada', 12, 3)->default(0.000);
            $table->decimal('precio_unitario', 12, 2);
            $table->decimal('descuento', 5, 2)->default(0.00);
            $table->decimal('subtotal', 12, 2);
            $table->string('unidad_medida', 20)->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedidos_venta_items');
        Schema::dropIfExists('pedidos_venta');
        Schema::dropIfExists('pedidos_compra_items');
        Schema::dropIfExists('pedidos_compra');
    }
};
