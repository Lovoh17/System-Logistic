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

            $table->index('activo');
        });

        // Ahora que almacenes existe, agregar FK a users
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('almacen_id')
                  ->nullable()
                  ->after('rol')
                  ->constrained('almacenes')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['almacen_id']);
            $table->dropColumn('almacen_id');
        });
        Schema::dropIfExists('almacenes');
    }
};
