<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('traslados', function (Blueprint $table) {
            $table->foreignId('transportista_id')
                ->nullable()
                ->after('almacen_destino_id')
                ->constrained('transportistas')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('traslados', function (Blueprint $table) {
            $table->dropConstrainedForeignId('transportista_id');
        });
    }
};
