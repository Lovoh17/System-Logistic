# 🚛 TraceLog — Sistema de Trazabilidad Logística

> **Proyecto académico** | Laravel 11 + Filament v3 | Cadena de Suministro

---

## 📋 Descripción del Sistema

**TraceLog** es un sistema completo de trazabilidad logística diseñado para empresas comerciales y distribuidoras en El Salvador. Permite controlar toda la cadena de suministro:

| Módulo | Funcionalidad |
|--------|---------------|
| 🏭 **Proveedores** | Registro, calificación, condiciones comerciales |
| 👥 **Clientes** | Base de clientes con crédito y tipos |
| 📦 **Inventario** | Stock en tiempo real, alertas, kardex |
| 🛒 **Pedidos de Compra** | Órdenes a proveedores con recepción |
| 📋 **Pedidos de Venta** | Gestión completa de órdenes de clientes |
| 🚚 **Transporte** | Flota propia y transportistas externos |
| 📍 **Envíos y Tracking** | Trazabilidad en tiempo real con eventos |
| 📊 **Dashboard** | KPIs, gráficos y alertas inteligentes |

---

## 🛠️ Requisitos

- PHP **8.2+**
- Composer **2.x**
- MySQL **8.0+** o MariaDB **10.6+**
- Node.js **18+** y NPM
- Laravel **11.x**

---

## 🚀 Instalación Paso a Paso

### 1. Clonar / Descomprimir el proyecto

```bash
cd /var/www  # o tu directorio de proyectos
```

### 2. Instalar dependencias PHP

```bash
composer install
```

### 3. Configurar variables de entorno

```bash
cp .env.example .env
php artisan key:generate
```

Editar `.env` con tus datos de base de datos:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tracelog_db
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_contraseña
```

### 4. Crear la base de datos

```sql
CREATE DATABASE tracelog_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 5. Ejecutar migraciones y seeders

```bash
php artisan migrate --seed
```

### 6. Crear enlace de almacenamiento

```bash
php artisan storage:link
```

### 7. Instalar dependencias frontend

```bash
npm install
npm run build
```

### 8. Iniciar el servidor

```bash
php artisan serve
```

Acceder a: **http://localhost:8000/admin**

---

## 🔐 Credenciales de Acceso

| Usuario | Email | Contraseña |
|---------|-------|------------|
| Administrador | admin@tracelog.com | password |
| Coordinador Logístico | logistica@tracelog.com | password |
| Ejecutivo de Ventas | ventas@tracelog.com | password |

---

## 🗄️ Estructura de la Base de Datos

```
proveedores          ← Empresas que nos venden
clientes             ← Empresas/personas a quienes vendemos
categorias           ← Clasificación de productos
productos            ← Catálogo con control de stock
almacenes            ← Bodegas / centros de distribución
pedidos_compra       ← Órdenes a proveedores
pedidos_compra_items ← Líneas de cada OC
pedidos_venta        ← Órdenes de clientes
pedidos_venta_items  ← Líneas de cada OV
transportistas       ← Flota y transportistas externos
envios               ← Control de despachos
seguimiento_envios   ← Historial de eventos de cada envío
movimientos_inv.     ← Kardex completo de inventario
```

---

## 📁 Estructura del Proyecto

```
app/
├── Filament/
│   ├── Pages/
│   │   └── Dashboard.php              ← Panel principal con KPIs
│   ├── Resources/
│   │   ├── ProveedorResource.php      ← CRUD Proveedores
│   │   ├── ClienteResource.php        ← CRUD Clientes
│   │   ├── ProductoResource.php       ← CRUD Productos + Stock
│   │   ├── PedidoCompraResource.php   ← Órdenes de Compra
│   │   ├── PedidoVentaResource.php    ← Órdenes de Venta
│   │   ├── TransportistaResource.php  ← Flota de transporte
│   │   └── EnvioResource.php          ← Envíos + Tracking
│   └── Widgets/
│       ├── EstadisticasWidget.php     ← KPIs principales
│       ├── GraficoVentasWidget.php    ← Gráfico de tendencias
│       ├── PedidosPendientesWidget.php
│       ├── StockCriticoWidget.php
│       └── EnviosActivosWidget.php
├── Models/                             ← Modelos Eloquent
├── Providers/
│   └── Filament/
│       └── AdminPanelProvider.php     ← Config del panel
database/
├── migrations/                        ← Estructura de BD
└── seeders/
    └── DatabaseSeeder.php             ← Datos de prueba SV
```

---

## 📱 Funcionalidades Destacadas

### Dashboard Inteligente
- KPIs en tiempo real: ventas, envíos, stock crítico
- Gráfico de ventas y entregas de los últimos 6 meses
- Alertas de pedidos urgentes y stock bajo
- Vista de envíos activos en tránsito

### Gestión de Pedidos
- Pedidos de compra con recepción parcial o total
- Pedidos de venta con múltiples estados de workflow
- Priorización: Normal, Alta, Urgente
- Múltiples canales de venta

### Trazabilidad Total de Envíos
- Registro de eventos de seguimiento en tiempo real
- Coordenadas GPS por envío
- Foto de entrega y firma del receptor
- Timeline completo del ciclo de vida del envío

### Control de Inventario
- Kardex automático con cada movimiento
- Alertas de stock mínimo y máximo
- 10 tipos de movimientos (compra, venta, ajuste, merma, etc.)
- Control por lotes y fechas de vencimiento

---

## 🎨 Tecnologías Utilizadas

| Tecnología | Versión | Uso |
|-----------|---------|-----|
| Laravel | 11.x | Framework PHP backend |
| Filament | 3.2 | Panel administrativo |
| MySQL | 8.0+ | Base de datos principal |
| Spatie Activity Log | 4.7 | Auditoría de cambios |
| Spatie Permissions | 6.4 | Roles y permisos |
| Laravel Excel | 3.1 | Exportación a Excel |
| DomPDF | 2.2 | Generación de PDFs |

---

## 📄 Licencia

Proyecto académico — Uso educativo.

---

*Desarrollado para el estudio de Trazabilidad Logística en Cadenas de Suministro*
