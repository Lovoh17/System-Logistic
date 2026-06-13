<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Agrega el estado "en_transito" al flujo de traslados.
     * Flujo: sugerido → aprobado → en_transito → completado (o cancelado).
     */
    public function up(): void
    {
        DB::statement(
            "ALTER TABLE traslados MODIFY COLUMN estado
             ENUM('sugerido','aprobado','en_transito','completado','cancelado')
             NOT NULL DEFAULT 'sugerido'"
        );
    }

    public function down(): void
    {
        // Revertir cualquier 'en_transito' a 'aprobado' antes de reducir el enum.
        DB::table('traslados')->where('estado', 'en_transito')->update(['estado' => 'aprobado']);

        DB::statement(
            "ALTER TABLE traslados MODIFY COLUMN estado
             ENUM('sugerido','aprobado','completado','cancelado')
             NOT NULL DEFAULT 'sugerido'"
        );
    }
};
