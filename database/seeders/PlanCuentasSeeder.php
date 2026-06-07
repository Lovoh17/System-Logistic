<?php

namespace Database\Seeders;

use App\Models\CuentaContable;
use Illuminate\Database\Seeder;

class PlanCuentasSeeder extends Seeder
{
    public function run(): void
    {
        $cuentas = [
            // ── 1. ACTIVO ──────────────────────────────────────────────────
            ['codigo' => '1',      'nombre' => 'ACTIVO',                           'tipo' => 'activo',  'naturaleza' => 'deudora',   'nivel' => 1, 'padre' => null,  'movible' => false],
            ['codigo' => '1.1',    'nombre' => 'Activo Circulante',                'tipo' => 'activo',  'naturaleza' => 'deudora',   'nivel' => 2, 'padre' => '1',   'movible' => false],
            ['codigo' => '1.1.01', 'nombre' => 'Caja General',                    'tipo' => 'activo',  'naturaleza' => 'deudora',   'nivel' => 3, 'padre' => '1.1', 'movible' => true],
            ['codigo' => '1.1.02', 'nombre' => 'Bancos',                          'tipo' => 'activo',  'naturaleza' => 'deudora',   'nivel' => 3, 'padre' => '1.1', 'movible' => true],
            ['codigo' => '1.1.03', 'nombre' => 'Cuentas por Cobrar Clientes',     'tipo' => 'activo',  'naturaleza' => 'deudora',   'nivel' => 3, 'padre' => '1.1', 'movible' => true],
            ['codigo' => '1.1.04', 'nombre' => 'IVA Crédito Fiscal',              'tipo' => 'activo',  'naturaleza' => 'deudora',   'nivel' => 3, 'padre' => '1.1', 'movible' => true],
            ['codigo' => '1.1.05', 'nombre' => 'Inventario de Mercadería',        'tipo' => 'activo',  'naturaleza' => 'deudora',   'nivel' => 3, 'padre' => '1.1', 'movible' => true],
            ['codigo' => '1.1.06', 'nombre' => 'Anticipo a Proveedores',          'tipo' => 'activo',  'naturaleza' => 'deudora',   'nivel' => 3, 'padre' => '1.1', 'movible' => true],
            ['codigo' => '1.2',    'nombre' => 'Activo No Circulante',            'tipo' => 'activo',  'naturaleza' => 'deudora',   'nivel' => 2, 'padre' => '1',   'movible' => false],
            ['codigo' => '1.2.01', 'nombre' => 'Mobiliario y Equipo',             'tipo' => 'activo',  'naturaleza' => 'deudora',   'nivel' => 3, 'padre' => '1.2', 'movible' => true],
            ['codigo' => '1.2.02', 'nombre' => 'Herramientas y Maquinaria',       'tipo' => 'activo',  'naturaleza' => 'deudora',   'nivel' => 3, 'padre' => '1.2', 'movible' => true],
            ['codigo' => '1.2.03', 'nombre' => 'Vehículos',                       'tipo' => 'activo',  'naturaleza' => 'deudora',   'nivel' => 3, 'padre' => '1.2', 'movible' => true],
            ['codigo' => '1.2.99', 'nombre' => 'Depreciación Acumulada',          'tipo' => 'activo',  'naturaleza' => 'acreedora', 'nivel' => 3, 'padre' => '1.2', 'movible' => true],

            // ── 2. PASIVO ──────────────────────────────────────────────────
            ['codigo' => '2',      'nombre' => 'PASIVO',                           'tipo' => 'pasivo',  'naturaleza' => 'acreedora', 'nivel' => 1, 'padre' => null,  'movible' => false],
            ['codigo' => '2.1',    'nombre' => 'Pasivo Circulante',               'tipo' => 'pasivo',  'naturaleza' => 'acreedora', 'nivel' => 2, 'padre' => '2',   'movible' => false],
            ['codigo' => '2.1.01', 'nombre' => 'Cuentas por Pagar Proveedores',  'tipo' => 'pasivo',  'naturaleza' => 'acreedora', 'nivel' => 3, 'padre' => '2.1', 'movible' => true],
            ['codigo' => '2.1.02', 'nombre' => 'IVA Débito Fiscal',              'tipo' => 'pasivo',  'naturaleza' => 'acreedora', 'nivel' => 3, 'padre' => '2.1', 'movible' => true],
            ['codigo' => '2.1.03', 'nombre' => 'Retenciones por Pagar',          'tipo' => 'pasivo',  'naturaleza' => 'acreedora', 'nivel' => 3, 'padre' => '2.1', 'movible' => true],
            ['codigo' => '2.1.04', 'nombre' => 'Sueldos por Pagar',              'tipo' => 'pasivo',  'naturaleza' => 'acreedora', 'nivel' => 3, 'padre' => '2.1', 'movible' => true],
            ['codigo' => '2.1.05', 'nombre' => 'Préstamos Bancarios Corto Plazo','tipo' => 'pasivo',  'naturaleza' => 'acreedora', 'nivel' => 3, 'padre' => '2.1', 'movible' => true],
            ['codigo' => '2.2',    'nombre' => 'Pasivo No Circulante',            'tipo' => 'pasivo',  'naturaleza' => 'acreedora', 'nivel' => 2, 'padre' => '2',   'movible' => false],
            ['codigo' => '2.2.01', 'nombre' => 'Préstamos Bancarios Largo Plazo','tipo' => 'pasivo',  'naturaleza' => 'acreedora', 'nivel' => 3, 'padre' => '2.2', 'movible' => true],

            // ── 3. CAPITAL ─────────────────────────────────────────────────
            ['codigo' => '3',      'nombre' => 'CAPITAL',                         'tipo' => 'capital', 'naturaleza' => 'acreedora', 'nivel' => 1, 'padre' => null,  'movible' => false],
            ['codigo' => '3.1',    'nombre' => 'Patrimonio',                      'tipo' => 'capital', 'naturaleza' => 'acreedora', 'nivel' => 2, 'padre' => '3',   'movible' => false],
            ['codigo' => '3.1.01', 'nombre' => 'Capital Social',                  'tipo' => 'capital', 'naturaleza' => 'acreedora', 'nivel' => 3, 'padre' => '3.1', 'movible' => true],
            ['codigo' => '3.1.02', 'nombre' => 'Utilidades Retenidas',            'tipo' => 'capital', 'naturaleza' => 'acreedora', 'nivel' => 3, 'padre' => '3.1', 'movible' => true],
            ['codigo' => '3.1.03', 'nombre' => 'Utilidad del Ejercicio',          'tipo' => 'capital', 'naturaleza' => 'acreedora', 'nivel' => 3, 'padre' => '3.1', 'movible' => true],

            // ── 4. INGRESOS ────────────────────────────────────────────────
            ['codigo' => '4',      'nombre' => 'INGRESOS',                        'tipo' => 'ingreso', 'naturaleza' => 'acreedora', 'nivel' => 1, 'padre' => null,  'movible' => false],
            ['codigo' => '4.1',    'nombre' => 'Ingresos de Operación',           'tipo' => 'ingreso', 'naturaleza' => 'acreedora', 'nivel' => 2, 'padre' => '4',   'movible' => false],
            ['codigo' => '4.1.01', 'nombre' => 'Ventas de Mercadería',            'tipo' => 'ingreso', 'naturaleza' => 'acreedora', 'nivel' => 3, 'padre' => '4.1', 'movible' => true],
            ['codigo' => '4.1.02', 'nombre' => 'Descuentos en Ventas',            'tipo' => 'ingreso', 'naturaleza' => 'deudora',   'nivel' => 3, 'padre' => '4.1', 'movible' => true],
            ['codigo' => '4.2',    'nombre' => 'Otros Ingresos',                  'tipo' => 'ingreso', 'naturaleza' => 'acreedora', 'nivel' => 2, 'padre' => '4',   'movible' => false],
            ['codigo' => '4.2.01', 'nombre' => 'Otros Ingresos',                  'tipo' => 'ingreso', 'naturaleza' => 'acreedora', 'nivel' => 3, 'padre' => '4.2', 'movible' => true],

            // ── 5. COSTOS ──────────────────────────────────────────────────
            ['codigo' => '5',      'nombre' => 'COSTOS',                          'tipo' => 'costo',   'naturaleza' => 'deudora',   'nivel' => 1, 'padre' => null,  'movible' => false],
            ['codigo' => '5.1',    'nombre' => 'Costo de Ventas',                 'tipo' => 'costo',   'naturaleza' => 'deudora',   'nivel' => 2, 'padre' => '5',   'movible' => false],
            ['codigo' => '5.1.01', 'nombre' => 'Costo de Ventas',                 'tipo' => 'costo',   'naturaleza' => 'deudora',   'nivel' => 3, 'padre' => '5.1', 'movible' => true],

            // ── 6. GASTOS ──────────────────────────────────────────────────
            ['codigo' => '6',      'nombre' => 'GASTOS',                          'tipo' => 'gasto',   'naturaleza' => 'deudora',   'nivel' => 1, 'padre' => null,  'movible' => false],
            ['codigo' => '6.1',    'nombre' => 'Gastos de Venta',                 'tipo' => 'gasto',   'naturaleza' => 'deudora',   'nivel' => 2, 'padre' => '6',   'movible' => false],
            ['codigo' => '6.1.01', 'nombre' => 'Sueldos y Salarios Venta',        'tipo' => 'gasto',   'naturaleza' => 'deudora',   'nivel' => 3, 'padre' => '6.1', 'movible' => true],
            ['codigo' => '6.1.02', 'nombre' => 'Publicidad y Mercadeo',           'tipo' => 'gasto',   'naturaleza' => 'deudora',   'nivel' => 3, 'padre' => '6.1', 'movible' => true],
            ['codigo' => '6.1.03', 'nombre' => 'Transporte y Entregas',           'tipo' => 'gasto',   'naturaleza' => 'deudora',   'nivel' => 3, 'padre' => '6.1', 'movible' => true],
            ['codigo' => '6.2',    'nombre' => 'Gastos Administrativos',          'tipo' => 'gasto',   'naturaleza' => 'deudora',   'nivel' => 2, 'padre' => '6',   'movible' => false],
            ['codigo' => '6.2.01', 'nombre' => 'Sueldos Administrativos',         'tipo' => 'gasto',   'naturaleza' => 'deudora',   'nivel' => 3, 'padre' => '6.2', 'movible' => true],
            ['codigo' => '6.2.02', 'nombre' => 'Alquiler de Local',               'tipo' => 'gasto',   'naturaleza' => 'deudora',   'nivel' => 3, 'padre' => '6.2', 'movible' => true],
            ['codigo' => '6.2.03', 'nombre' => 'Servicios Públicos (Agua/Luz)',   'tipo' => 'gasto',   'naturaleza' => 'deudora',   'nivel' => 3, 'padre' => '6.2', 'movible' => true],
            ['codigo' => '6.2.04', 'nombre' => 'Depreciaciones',                  'tipo' => 'gasto',   'naturaleza' => 'deudora',   'nivel' => 3, 'padre' => '6.2', 'movible' => true],
            ['codigo' => '6.2.05', 'nombre' => 'Papelería y Útiles',              'tipo' => 'gasto',   'naturaleza' => 'deudora',   'nivel' => 3, 'padre' => '6.2', 'movible' => true],
            ['codigo' => '6.3',    'nombre' => 'Gastos Financieros',              'tipo' => 'gasto',   'naturaleza' => 'deudora',   'nivel' => 2, 'padre' => '6',   'movible' => false],
            ['codigo' => '6.3.01', 'nombre' => 'Intereses Bancarios',             'tipo' => 'gasto',   'naturaleza' => 'deudora',   'nivel' => 3, 'padre' => '6.3', 'movible' => true],
            ['codigo' => '6.3.02', 'nombre' => 'Comisiones Bancarias',            'tipo' => 'gasto',   'naturaleza' => 'deudora',   'nivel' => 3, 'padre' => '6.3', 'movible' => true],
        ];

        // Primera pasada: insertar todas las cuentas sin padre
        $idMap = [];
        foreach ($cuentas as $data) {
            if ($data['padre'] === null) {
                $cuenta = CuentaContable::firstOrCreate(
                    ['codigo' => $data['codigo']],
                    [
                        'nombre'             => $data['nombre'],
                        'tipo'               => $data['tipo'],
                        'naturaleza'         => $data['naturaleza'],
                        'nivel'              => $data['nivel'],
                        'acepta_movimientos' => $data['movible'],
                        'activa'             => true,
                    ]
                );
                $idMap[$data['codigo']] = $cuenta->id;
            }
        }

        // Segunda pasada: cuentas con padre
        foreach ($cuentas as $data) {
            if ($data['padre'] !== null) {
                $padreId = $idMap[$data['padre']] ?? null;
                $cuenta = CuentaContable::firstOrCreate(
                    ['codigo' => $data['codigo']],
                    [
                        'nombre'             => $data['nombre'],
                        'tipo'               => $data['tipo'],
                        'naturaleza'         => $data['naturaleza'],
                        'nivel'              => $data['nivel'],
                        'cuenta_padre_id'    => $padreId,
                        'acepta_movimientos' => $data['movible'],
                        'activa'             => true,
                    ]
                );
                $idMap[$data['codigo']] = $cuenta->id;
            }
        }
    }
}
