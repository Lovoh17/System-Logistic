<?php

namespace Tests\Feature;

use App\Models\Almacen;
use App\Models\MovimientoInventario;
use App\Models\PedidoVenta;
use App\Models\PedidoVentaItem;
use App\Models\Producto;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Concerns\SeedsInventory;
use Tests\TestCase;

class PedidoVentaObserverTest extends TestCase
{
    use DatabaseTransactions;
    use SeedsInventory;

    /**
     * Crea un pedido de venta en estado "borrador" con una línea.
     *
     * @return array{pedido: PedidoVenta, almacen: Almacen, producto: Producto}
     */
    private function crearPedido(float $cantidad, float $stockInicial): array
    {
        $user = $this->crearUsuario();
        $this->actingAs($user);

        $almacen = $this->crearAlmacen('Sucursal');
        $producto = $this->crearProducto();
        $cliente = $this->crearCliente();
        $this->crearInventario($producto, $almacen, $stockInicial);

        $pedido = PedidoVenta::create([
            'numero' => PedidoVenta::generarNumero(),
            'cliente_id' => $cliente->id,
            'user_id' => $user->id,
            'almacen_id' => $almacen->id,
            'fecha_pedido' => now(),
            'estado' => 'borrador',
            'subtotal' => 20 * $cantidad,
            'impuesto' => 0,
            'total' => 20 * $cantidad,
            'moneda' => 'USD',
        ]);

        PedidoVentaItem::create([
            'pedido_venta_id' => $pedido->id,
            'producto_id' => $producto->id,
            'cantidad' => $cantidad,
            'precio_unitario' => 20,
            'subtotal' => 20 * $cantidad,
        ]);

        return compact('pedido', 'almacen', 'producto');
    }

    public function test_confirmar_descuenta_stock_y_registra_kardex(): void
    {
        ['pedido' => $pedido, 'almacen' => $almacen, 'producto' => $p] = $this->crearPedido(15, 50);

        $pedido->update(['estado' => 'confirmado']);

        $this->assertSame(35.0, $this->stockActual($p, $almacen));
        $this->assertDatabaseHas('movimientos_inventario', [
            'tipo' => 'salida_venta',
            'producto_id' => $p->id,
            'almacen_id' => $almacen->id,
            'referencia_type' => PedidoVenta::class,
            'referencia_id' => $pedido->id,
        ]);

        // La línea queda marcada como despachada.
        $this->assertSame(15.0, (float) $pedido->items()->first()->cantidad_despachada);
    }

    public function test_no_descuenta_dos_veces_al_avanzar_de_estado(): void
    {
        ['pedido' => $pedido, 'almacen' => $almacen, 'producto' => $p] = $this->crearPedido(15, 50);

        $pedido->update(['estado' => 'confirmado']);       // 50 -> 35
        $pedido->update(['estado' => 'en_preparacion']);    // sin cambio
        $pedido->update(['estado' => 'entregado']);         // sin cambio

        $this->assertSame(35.0, $this->stockActual($p, $almacen));
        $this->assertSame(1, MovimientoInventario::where('referencia_id', $pedido->id)
            ->where('tipo', 'salida_venta')->count());
    }

    public function test_cancelar_despues_de_confirmar_restaura_stock(): void
    {
        ['pedido' => $pedido, 'almacen' => $almacen, 'producto' => $p] = $this->crearPedido(15, 50);

        $pedido->update(['estado' => 'confirmado']);   // 50 -> 35
        $pedido->update(['estado' => 'cancelado']);     // restaura -> 50

        $this->assertSame(50.0, $this->stockActual($p, $almacen));
        $this->assertDatabaseHas('movimientos_inventario', [
            'tipo' => 'devolucion_venta',
            'referencia_id' => $pedido->id,
        ]);
    }

    public function test_cancelar_desde_borrador_no_mueve_stock(): void
    {
        ['pedido' => $pedido, 'almacen' => $almacen, 'producto' => $p] = $this->crearPedido(15, 50);

        $pedido->update(['estado' => 'cancelado']);

        $this->assertSame(50.0, $this->stockActual($p, $almacen));
        $this->assertSame(0, MovimientoInventario::where('referencia_id', $pedido->id)->count());
    }
}
