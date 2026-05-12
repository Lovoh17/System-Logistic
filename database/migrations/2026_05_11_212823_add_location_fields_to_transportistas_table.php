<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transportistas', function (Blueprint $table) {
            $table->decimal('latitud', 10, 8)->nullable()->after('estado');
            $table->decimal('longitud', 11, 8)->nullable()->after('latitud');
            $table->string('ubicacion_actual')->nullable()->after('longitud');
            $table->timestamp('ultima_ubicacion_at')->nullable()->after('ubicacion_actual');
        });
    }

    public function down(): void
    {
        Schema::table('transportistas', function (Blueprint $table) {
            $table->dropColumn(['latitud', 'longitud', 'ubicacion_actual', 'ultima_ubicacion_at']);
        });
    }
};