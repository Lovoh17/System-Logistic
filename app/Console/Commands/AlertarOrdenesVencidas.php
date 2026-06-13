<?php

namespace App\Console\Commands;

use App\Models\PedidoCompra;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Console\Command;

class AlertarOrdenesVencidas extends Command
{
    protected $signature = 'compras:alertar-ordenes-vencidas';

    protected $description = 'Envía notificaciones de base de datos para órdenes de compra que llevan más de 7 días sin recibir.';

    public function handle(): int
    {
        $ordenes = PedidoCompra::whereIn('estado', ['enviado', 'confirmado'])
            ->where('fecha_pedido', '<', now()->subDays(7))
            ->with('proveedor')
            ->get();

        if ($ordenes->isEmpty()) {
            $this->info('Sin órdenes vencidas. Nada que notificar.');

            return self::SUCCESS;
        }

        // Notificar a todos los usuarios con rol super_admin o logistica.
        $usuarios = User::role(['super_admin', 'logistica'])->get();

        if ($usuarios->isEmpty()) {
            $usuarios = User::all();
        }

        foreach ($ordenes as $orden) {
            $proveedorNombre = $orden->proveedor?->nombre ?? '(sin proveedor)';
            $titulo = "OC Vencida: {$orden->numero}";
            $cuerpo = "La orden {$orden->numero} del proveedor \"{$proveedorNombre}\" "
                     .'lleva más de 7 días pendiente de recepción.';

            foreach ($usuarios as $usuario) {
                Notification::make()
                    ->title($titulo)
                    ->body($cuerpo)
                    ->warning()
                    ->icon('heroicon-o-exclamation-triangle')
                    ->sendToDatabase($usuario);
            }

            $this->line(" → {$orden->numero} ({$proveedorNombre}) — notificado a {$usuarios->count()} usuario(s).");
        }

        $this->info("Total: {$ordenes->count()} orden(es) vencida(s) notificada(s).");

        return self::SUCCESS;
    }
}
