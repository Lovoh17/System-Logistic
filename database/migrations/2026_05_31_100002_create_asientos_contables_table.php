<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asientos_contables', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 25)->unique()->comment('ASI-2026-0001');
            $table->date('fecha');
            $table->text('descripcion');
            $table->enum('tipo_documento', [
                'factura_cf',
                'ccf',
                'nota_debito',
                'nota_credito',
                'comprobante_interno',
                'transferencia',
            ])->default('comprobante_interno');
            $table->string('numero_documento', 60)->nullable()->comment('Número de la factura o CCF de respaldo');
            $table->decimal('total_debe',  14, 2)->default(0);
            $table->decimal('total_haber', 14, 2)->default(0);
            $table->enum('estado', ['borrador', 'registrado', 'anulado'])->default('borrador');
            $table->string('origen_tipo', 60)->nullable()->comment('PedidoVenta | PedidoCompra | MovimientoInventario');
            $table->unsignedBigInteger('origen_id')->nullable();
            $table->text('notas')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['fecha', 'estado']);
            $table->index(['origen_tipo', 'origen_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asientos_contables');
    }
};
