<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pedidos_compra', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 20)->unique();
            $table->foreignId('proveedor_id')->constrained('proveedores');
            $table->foreignId('user_id')->constrained('users'); // creador

            // Fechas del ciclo de vida
            $table->date('fecha_pedido');
            $table->date('fecha_requerida')->nullable();
            $table->date('fecha_recepcion')->nullable();

            $table->enum('estado', [
                'borrador', 'enviado', 'confirmado', 'parcial', 'recibido', 'cancelado',
            ])->default('borrador');

            // Totales
            $table->decimal('subtotal', 12, 2)->default(0.00);
            $table->decimal('impuesto', 12, 2)->default(0.00);
            $table->decimal('descuento', 12, 2)->default(0.00);
            $table->decimal('total', 12, 2)->default(0.00);
            $table->char('moneda', 3)->default('USD');

            $table->text('condiciones_pago')->nullable();
            $table->text('notas')->nullable();
            $table->text('motivo_cancelacion')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['estado', 'fecha_pedido']);
            $table->index('proveedor_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedidos_compra');
    }
};
