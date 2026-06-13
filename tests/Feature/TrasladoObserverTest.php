<?php

namespace Tests\Feature;

use App\Models\Almacen;
use App\Models\Producto;
use App\Models\Traslado;
use App\Models\TrasladoItem;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Concerns\SeedsInventory;
use Tests\TestCase;

class TrasladoObserverTest extends TestCase
{
    use DatabaseTransactions;
    use SeedsInventory;

    /**
     * Crea un traslado "aprobado" de $cantidad unidades de un producto,
     * con stock inicial en origen y destino.
     *
     * @return array{traslado: Traslado, origen: Almacen, destino: Almacen, producto: Producto}
     */
    private function crearTraslado(float $cantidad, float $stockOrigen, float $stockDestino = 0): array
    {
        $user = $this->crearUsuario();
        $this->actingAs($user);

        $origen = $this->crearAlmacen('Origen');
        $destino = $this->crearAlmacen('Destino');
        $producto = $this->crearProducto();

        $this->crearInventario($producto, $origen, $stockOrigen);
        if ($stockDestino > 0) {
            $this->crearInventario($producto, $destino, $stockDestino);
        }

        $traslado = Traslado::create([
            'numero' => Traslado::generarNumero(),
            'almacen_origen_id' => $origen->id,
            'almacen_destino_id' => $destino->id,
            'estado' => 'aprobado',
            'motivo' => 'Traslado de prueba',
            'creado_por' => $user->id,
        ]);

        TrasladoItem::create([
            'traslado_id' => $traslado->id,
            'producto_id' => $producto->id,
            'cantidad_sugerida' => $cantidad,
        ]);

        return compact('traslado', 'origen', 'destino', 'producto');
    }

    public function test_en_transito_descuenta_del_origen(): void
    {
        ['traslado' => $t, 'origen' => $origen, 'producto' => $p] = $this->crearTraslado(20, 100);

        $t->update(['estado' => 'en_transito']);

        $this->assertSame(80.0, $this->stockActual($p, $origen));
        $this->assertDatabaseHas('movimientos_inventario', [
            'tipo' => 'traslado_salida',
            'producto_id' => $p->id,
            'almacen_id' => $origen->id,
            'referencia_type' => Traslado::class,
            'referencia_id' => $t->id,
        ]);
    }

    public function test_completado_incrementa_el_destino(): void
    {
        ['traslado' => $t, 'destino' => $destino, 'producto' => $p] = $this->crearTraslado(20, 100, 5);

        $t->update(['estado' => 'en_transito']);
        $t->update(['estado' => 'completado']);

        // Destino: 5 inicial + 20 recibidos.
        $this->assertSame(25.0, $this->stockActual($p, $destino));
        $this->assertDatabaseHas('movimientos_inventario', [
            'tipo' => 'traslado_entrada',
            'almacen_id' => $destino->id,
            'referencia_id' => $t->id,
        ]);
    }

    public function test_cancelar_desde_completado_revierte_ambos_lados(): void
    {
        ['traslado' => $t, 'origen' => $origen, 'destino' => $destino, 'producto' => $p]
            = $this->crearTraslado(20, 100, 5);

        $t->update(['estado' => 'en_transito']);   // origen 100 -> 80
        $t->update(['estado' => 'completado']);     // destino 5 -> 25
        $t->update(['estado' => 'cancelado']);      // revierte ambos

        // Origen vuelve a 100, destino vuelve a 5.
        $this->assertSame(100.0, $this->stockActual($p, $origen));
        $this->assertSame(5.0, $this->stockActual($p, $destino));
    }

    public function test_cancelar_desde_aprobado_no_mueve_stock(): void
    {
        ['traslado' => $t, 'origen' => $origen, 'producto' => $p] = $this->crearTraslado(20, 100);

        $t->update(['estado' => 'cancelado']);

        $this->assertSame(100.0, $this->stockActual($p, $origen));
    }
}
