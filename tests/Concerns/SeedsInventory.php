<?php

namespace Tests\Concerns;

use App\Models\Almacen;
use App\Models\Cliente;
use App\Models\InventarioAlmacen;
use App\Models\Producto;
use App\Models\User;

/**
 * Helpers para construir datos mínimos de inventario en los tests.
 */
trait SeedsInventory
{
    protected function crearAlmacen(string $nombre = 'Almacén'): Almacen
    {
        return Almacen::create([
            'codigo' => 'ALM-'.uniqid(),
            'nombre' => $nombre,
            'activo' => true,
        ]);
    }

    protected function crearProducto(float $precioCompra = 10, float $precioVenta = 20): Producto
    {
        return Producto::create([
            'codigo' => 'PRD-'.uniqid(),
            'nombre' => 'Producto de prueba',
            'precio_compra' => $precioCompra,
            'precio_venta' => $precioVenta,
            'estado' => 'activo',
        ]);
    }

    protected function crearInventario(Producto $producto, Almacen $almacen, float $stock, float $min = 0, float $max = 0): InventarioAlmacen
    {
        return InventarioAlmacen::create([
            'producto_id' => $producto->id,
            'almacen_id' => $almacen->id,
            'stock_actual' => $stock,
            'stock_minimo' => $min,
            'stock_maximo' => $max,
            'punto_reorden' => 0,
        ]);
    }

    protected function crearUsuario(): User
    {
        return User::factory()->create();
    }

    protected function crearCliente(): Cliente
    {
        return Cliente::create([
            'codigo' => 'CLI-'.uniqid(),
            'nombre' => 'Cliente de prueba',
            'estado' => 'activo',
            'pais' => 'El Salvador',
        ]);
    }

    /** Stock actual de un (producto, almacén). */
    protected function stockActual(Producto $producto, Almacen $almacen): float
    {
        return (float) InventarioAlmacen::where('producto_id', $producto->id)
            ->where('almacen_id', $almacen->id)
            ->value('stock_actual');
    }
}
