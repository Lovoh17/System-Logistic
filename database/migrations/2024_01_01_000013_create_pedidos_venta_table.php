<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pedidos_venta', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 20)->unique();
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('almacen_id')
                  ->nullable()
                  ->constrained('almacenes')
                  ->nullOnDelete();

            $table->date('fecha_pedido');
            $table->date('fecha_requerida')->nullable();
            $table->date('fecha_entrega_real')->nullable();

            $table->enum('estado', [
                'borrador', 'confirmado', 'en_preparacion',
                'listo', 'en_transito', 'entregado', 'cancelado', 'devolucion',
            ])->default('borrador');

            $table->enum('prioridad', ['baja', 'normal', 'alta', 'urgente'])->default('normal');

            $table->decimal('subtotal', 12, 2)->default(0.00);
            $table->decimal('impuesto', 12, 2)->default(0.00);
            $table->decimal('descuento', 12, 2)->default(0.00);
            $table->decimal('costo_envio', 12, 2)->default(0.00);
            $table->decimal('total', 12, 2)->default(0.00);
            $table->char('moneda', 3)->default('USD');

            $table->foreignId('direccion_entrega_id')
                  ->nullable()
                  ->constrained('direcciones_clientes')
                  ->nullOnDelete();

            $table->text('direccion_entrega')->nullable();
            $table->string('departamento_entrega', 80)->nullable();
            $table->string('municipio_entrega', 80)->nullable();
            $table->text('instrucciones_entrega')->nullable();

            $table->string('canal_venta', 30)->default('directo');
            $table->text('notas')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['estado', 'fecha_pedido']);
            $table->index(['cliente_id', 'estado']);
            $table->index('prioridad');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedidos_venta');
    }
};
