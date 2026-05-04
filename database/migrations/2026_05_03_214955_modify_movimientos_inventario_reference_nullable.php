<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movimientos_inventario', function (Blueprint $table) {
            // Modificar las columnas de referencia para que permitan NULL
            $table->string('referencia_type')->nullable()->change();
            $table->unsignedBigInteger('referencia_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('movimientos_inventario', function (Blueprint $table) {
            $table->string('referencia_type')->nullable(false)->change();
            $table->unsignedBigInteger('referencia_id')->nullable(false)->change();
        });
    }
};