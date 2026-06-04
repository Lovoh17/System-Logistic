<?php

namespace App\Observers;

use App\Models\PedidoCompra;

class PedidoCompraObserver
{
    /**
     * La lógica de actualización de inventario al recibir una OC
     * se gestiona directamente en la acción `confirmar_recepcion`
     * del PedidoCompraResource, donde se capturan: almacén destino,
     * lote, fecha de vencimiento y cantidades parciales por ítem.
     *
     * El registro de actividad (quién creó/modificó/canceló) lo maneja
     * automáticamente el trait LogsActivity del modelo PedidoCompra.
     */
    public function updated(PedidoCompra $pedidoCompra): void
    {
        // Reservado para eventos futuros no cubiertos por la acción UI.
    }
}
