<?php

namespace Tests\Feature;

use App\Models\MovimientoInventario;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Concerns\SeedsInventory;
use Tests\TestCase;

class InventoryServiceTest extends TestCase
{
    use DatabaseTransactions;
    use SeedsInventory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs($this->crearUsuario());
    }

    private function service(): InventoryService
    {
        return app(InventoryService::class);
    }

    public function test_salida_descuenta_stock_y_registra_kardex(): void
    {
        $almacen = $this->crearAlmacen();
        $producto = $this->crearProducto();
        $this->crearInventario($producto, $almacen, 100);

        $mov = $this->service()->salida(
            $producto->id, $almacen->id, 30, 'salida_venta', 'Salida de prueba de inventario'
        );

        $this->assertSame(70.0, $this->stockActual($producto, $almacen));
        $this->assertDatabaseHas('movimientos_inventario', [
            'id' => $mov->id,
            'tipo' => 'salida_venta',
            'producto_id' => $producto->id,
            'almacen_id' => $almacen->id,
        ]);
        $this->assertSame(30.0, (float) $mov->cantidad);
        $this->assertSame(100.0, (float) $mov->stock_anterior);
        $this->assertSame(70.0, (float) $mov->stock_nuevo);
    }

    public function test_entrada_incrementa_stock_y_registra_kardex(): void
    {
        $almacen = $this->crearAlmacen();
        $producto = $this->crearProducto();
        $this->crearInventario($producto, $almacen, 10);

        $this->service()->entrada(
            $producto->id, $almacen->id, 5, 'traslado_entrada', 'Entrada de prueba de inventario'
        );

        $this->assertSame(15.0, $this->stockActual($producto, $almacen));
        $this->assertSame(1, MovimientoInventario::where('tipo', 'traslado_entrada')->count());
    }

    public function test_entrada_crea_inventario_si_no_existe(): void
    {
        $almacen = $this->crearAlmacen();
        $producto = $this->crearProducto();

        // Sin fila previa en inventario_almacen.
        $this->service()->entrada(
            $producto->id, $almacen->id, 8, 'traslado_entrada', 'Entrada inicial de prueba'
        );

        $this->assertSame(8.0, $this->stockActual($producto, $almacen));
    }
}
