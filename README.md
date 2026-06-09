
# TraceLog — Sistema de trazabilidad logística

Proyecto académico construido con Laravel 11 y Filament v3 para gestión y trazabilidad de la cadena de suministro. Este repositorio contiene la aplicación backend y las configuraciones del panel administrativo.

Resumen: TraceLog permite gestionar proveedores, clientes, productos, almacenes, pedidos (compra y venta), movimientos de inventario, envíos y seguimiento de eventos asociados a cada despacho.

Requisitos
---------

- PHP 8.2 o superior
- Composer 2.x
- MySQL 8.0+ o MariaDB 10.6+
- Node.js 18+ y npm
- Sistema operativo: Linux / macOS / Windows (se incluyen comandos orientativos para PowerShell)

Instalación (PowerShell)
------------------------

1) Clonar el repositorio y entrar en la carpeta del proyecto

```powershell
git clone <url-del-repositorio>
Set-Location C:\ruta\a\System-Logistic
```

2) Instalar dependencias PHP

```powershell
composer install --no-interaction --prefer-dist
```

3) Preparar el archivo de entorno y generar la clave de aplicación

```powershell
Copy-Item .env.example .env
php artisan key:generate
```

Edita `.env` con los datos de tu base de datos y otros ajustes (DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD, MAIL settings, etc.).

4) Crear la base de datos (ejemplo MySQL)

Ejecuta en tu cliente MySQL:

```sql
CREATE DATABASE tracelog_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

5) Ejecutar migraciones y seeders

```powershell
php artisan migrate --seed
```

6) Crear enlace simbólico para storage

```powershell
php artisan storage:link
```

7) Instalar dependencias frontend y compilar (opcional en desarrollo)

```powershell
npm install
npm run build
```

8) Iniciar servidor de desarrollo

```powershell
php artisan serve --host=127.0.0.1 --port=8000
```

Accede al panel administrativo en: http://127.0.0.1:8000/admin

Credenciales de ejemplo
-----------------------

En el seeder de ejemplo se incluyen usuarios de prueba. Valores típicos utilizados en desarrollo:

- Administrador: admin@tracelog.com / password
- Coordinador logístico: logistica@tracelog.com / password
- Ejecutivo de ventas: ventas@tracelog.com / password

Estructura general del proyecto
-------------------------------

Contenido relevante del repositorio (rutas principales):

- `app/` — Código de la aplicación
  - `app/Filament/` — Recursos, páginas y widgets del panel Filament
  - `app/Http/` — Requests, controllers, middleware y respuestas personalizadas
  - `app/Models/` — Modelos Eloquent (Producto, PedidoCompra, PedidoVenta, Envio, Traslado, etc.)
  - `app/Observers/` — Observers para eventos de modelos
  - `app/Services/` — Servicios reutilizables (Asiento contable, cálculo de distancias, etc.)

- `bootstrap/` — Arranque de la aplicación
- `config/` — Archivos de configuración (app, permission, telescope, etc.)
- `database/` — Migrations y seeders
- `public/` — Activos públicos (index.php, css, js, imágenes)
- `resources/views/` — Vistas Blade si aplica
- `routes/` — Rutas web, api, console y channels
- `storage/` — Archivos generados, logs y caches

Modelos y dominio
------------------

El proyecto contiene modelos que representan las entidades del dominio logístico:

- `Producto`, `Categoria`, `Almacen`, `InventarioAlmacen`
- `PedidoCompra`, `PedidoCompraItem`, `PedidoVenta`, `PedidoVentaItem`
- `Proveedor`, `Cliente`, `DireccionCliente`
- `Traslado`, `TrasladoItem`, `MovimientoInventario`
- `Envio`, `SeguimientoEnvio`, `Transportista`, `DistanciaSucursal`
- `AsientoContable`, `LineaAsiento`, `CuentaContable` (para contabilidad básica)

Funcionalidades principales
--------------------------

- Gestión completa de proveedores, clientes y productos
- Módulo de inventario con movimientos automáticos (kardex)
- Pedidos de compra y venta con recepción/entrega parcial
- Registro y seguimiento de envíos con eventos y geolocalización
- Panel administrativo con recursos Filament y widgets para KPIs
- Exportes a Excel y generación de PDFs
- Registro de actividad y control de permisos (Spatie)

Buenas prácticas y consideraciones
---------------------------------

- Mantener las credenciales y secretos fuera del repositorio (.env no versionado)
- Ejecutar migraciones en un entorno controlado y revisar seeders antes de correr en producción
- Revisar configuraciones de correo, colas y almacenamiento para integrarlas con servicios reales en producción

Tecnologías y dependencias destacadas
------------------------------------

- Laravel 11 (backend)
- Filament v3 (panel administrativo)
- Spatie packages (activity log, permissions)
- Laravel Excel, DomPDF para exportes
- Livewire y/o Alpine (según componentes usados en Filament)

Licencia
--------

Proyecto académico — uso educativo.

Contacto
--------

Para preguntas o contribuciones, abre un issue o contacta al autor del repositorio.

