<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categorias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('slug', 120)->unique();
            $table->text('descripcion')->nullable();
            $table->string('icono', 50)->nullable();
            $table->string('color', 20)->nullable();
            // Auto-referencia para categorías padre/hijo
            $table->foreignId('parent_id')
                  ->nullable()
                  ->constrained('categorias')
                  ->nullOnDelete();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index('activo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categorias');
    }
};
