<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lineas_asiento', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asiento_contable_id')->constrained('asientos_contables')->cascadeOnDelete();
            $table->foreignId('cuenta_contable_id')->constrained('cuentas_contables');
            $table->string('descripcion', 255)->nullable();
            $table->decimal('debe',  14, 2)->default(0);
            $table->decimal('haber', 14, 2)->default(0);
            $table->tinyInteger('orden')->default(0);
            $table->timestamps();

            $table->index('asiento_contable_id');
            $table->index('cuenta_contable_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lineas_asiento');
    }
};
