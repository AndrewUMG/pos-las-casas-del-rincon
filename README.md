# Sistema POS e Inventario – Las Casas del Rincón Guatemalteco

Proyecto final del curso **Desarrollo Web**. Punto de venta e inventario para la venta de productos a
colonias residenciales (Pastas, Productos Cárnicos, Embutidos, Lácteos, Golosinas, Aguas Gaseosas y Carbonatados).

- **Tecnología:** PHP 8.3 + Laravel 13 (MVC, vistas Blade + Bootstrap 5) y PostgreSQL 16.
- **Arquitectura:** DDD + Arquitectura Hexagonal (puertos y adaptadores), tal como se documentó en los modelos C4,
  los diagramas de casos de uso y los diagramas de arquitectura hexagonal.

**Documentación de diseño:** los diagramas C4, de casos de uso y de arquitectura hexagonal están en [`docs/`](docs/README.md).
**Kanban:** <https://github.com/users/AndrewUMG/projects/1>

## Requisitos del documento y dónde se cumplen

| # | Requisito | Módulo / pantalla |
|---|-----------|-------------------|
| 1 | Interfaz de acceso (todo a nivel de base de datos) | `Seguridad` – `/login` (credenciales validadas contra la tabla `usuarios`) |
| 2 | Catálogos: clientes, proveedores, productos y precios | `Catálogos` – `/clientes`, `/proveedores`, `/productos`, `/precios` |
| 3 | Despacho: lectura, cobro y subtotal por línea, total de la compra | `Despacho` – `/pos` (lector de código de barras) |
| 4 | Devoluciones por daño o vencimiento | `Devoluciones` – `/devoluciones` |
| 5 | Cobro con tarjeta de crédito, débito o efectivo | `Cobros` – `/pos/{venta}/cobro` |
| 6 | Facturación con datos del cliente y todos los productos | `Facturación` – `/facturas/{id}` (pantalla, impresión y PDF) |
| 7 | Reportes: cierre del día, devoluciones, clientes, compras para existencias | `Reportes` – `/reportes` (pantalla y PDF) |
| 8 | Código QR en la factura que indica la promoción al leerlo con el celular | `Promociones` – `/promocion/{codigo}` (página pública) |
| 9 | Menú interactivo por módulos | Barra de menú superior; cada opción se muestra según el rol |
| 10 | Apertura de caja y corte de caja | `Caja` – `/caja` |
| 10a | Promociones: producto gratuito, vale de regalo, 2.º a mitad de precio, mayoreo, puntos | `Promociones` – `/promociones` (automáticas o entregadas por QR) |
| — | Control del inventario que ingresa y que se vende | `Inventario` – `/compras`, `/existencias`, kardex |
| — | Core de Empresas de Consumo Masivo (existente) | `Catálogos › Sincronizar con el Core` – `/core` |

## Puesta en marcha

### 1. Base de datos (PostgreSQL con Docker)

```bash
docker compose up -d
```

Crea el contenedor `pos_rincon_postgres` en el puerto **5433** (base `pos_rincon`, usuario `pos_user`, clave `pos_secret`).

### 2. Activar la extensión de PostgreSQL en PHP (Laragon)

En Laragon: **Menú › PHP › Extensions** y marcar `pdo_pgsql` y `pgsql`.
(Equivale a quitar el `;` de `extension=pdo_pgsql` y `extension=pgsql` en `php.ini`.)

### 3. Instalar y preparar

```bash
composer install
php artisan migrate:fresh --seed
php artisan serve
```

Abrir <http://127.0.0.1:8000>.

### Usuarios de prueba (creados por el seeder)

| Usuario | Contraseña | Rol | Menú |
|---------|------------|-----|------|
| `admin` | `admin123` | Administrador | Todos los módulos |
| `cajero` | `cajero123` | Cajero | Punto de Venta, Caja y Devoluciones |

Los datos iniciales incluyen las 7 familias, 5 proveedores, 23 productos con código de barras
(existencia inicial registrada como compra, por lo que aparece en el kardex), 3 clientes y una promoción de cada tipo.

### Leer el QR con el celular

El QR de la factura contiene la URL `APP_URL/promocion/{código}`. Para abrirla desde un celular en la misma red,
inicie el servidor con `php artisan serve --host=0.0.0.0` y cambie `APP_URL` en `.env` por la IP de la computadora
(por ejemplo `http://192.168.1.50:8000`).

## Flujo de una venta

1. **Caja › Apertura**: el cajero indica el monto inicial.
2. **Punto de Venta › Despacho**: escanea (o escribe) el código de cada producto; se muestran subtotal por línea,
   descuentos de las promociones automáticas y el total. Opcional: asignar cliente por NIT, canjear el cupón QR de
   una compra anterior o pagar con puntos.
3. **Cobrar**: efectivo (calcula el cambio), tarjeta de crédito o débito (referencia del voucher).
   Se descuentan las existencias y se registra el ingreso en la caja.
4. **Factura**: NIT, nombre y dirección (o CF). La factura incluye un **código QR** con una promoción
   asignada al azar entre las promociones vigentes de tipo QR. El cliente la consulta con su celular y la
   canjea en su próxima compra (un solo uso, con vencimiento).
5. **Caja › Cerrar caja**: se ingresa el efectivo contado y se genera el **corte de caja**
   (esperado = monto inicial + ventas en efectivo − devoluciones).

## Arquitectura hexagonal

```
app/
├── Domain/                 Núcleo: entidades y reglas de negocio en PHP puro (sin Laravel)
│   ├── Ventas/             Venta (raíz de agregado), LineaVenta
│   ├── Promociones/        Promocion, TipoPromocion, MotorPromociones, Cupon
│   ├── Caja/  Cobros/  Facturacion/  Devoluciones/  Inventario/  Catalogos/  Seguridad/
│   └── Shared/             ReglaNegocioException, Dinero
├── Application/            Casos de uso
│   └── {Módulo}/
│       ├── Ports/In/       Puertos de entrada (interfaces de los casos de uso)
│       ├── Ports/Out/      Puertos de salida (lo que el dominio necesita)
│       └── Services/       Implementación de los casos de uso
├── Infrastructure/         Adaptadores
│   ├── Http/               Adaptadores primarios: controladores, middleware de permisos, presentadores
│   ├── Persistence/        Adaptadores secundarios: repositorios Eloquent (PostgreSQL) y consultas de reportes
│   ├── Integracion/        Adaptadores entre contextos (Despacho → Inventario, Cobros → Caja, … )
│   ├── External/           Core de Empresas de Consumo Masivo (archivo de intercambio o API HTTP)
│   ├── Qr/  Pdf/  Security/  Shared/
└── Providers/HexagonalServiceProvider.php   Ensamblaje: cada puerto se conecta con su adaptador
```

La dependencia siempre apunta hacia adentro: `Infrastructure → Application → Domain`.

### Core de Empresas de Consumo Masivo

El puerto `CatalogoCorePort` tiene dos adaptadores, seleccionables en `.env`:

- `CORE_DRIVER=archivo` (predeterminado): lee el catálogo exportado por el Core desde `storage/app/core/catalogo.json`.
- `CORE_DRIVER=http`: consume la API del Core en `CORE_API_URL` (con `CORE_API_TOKEN` opcional).

## Pruebas

```bash
php artisan test
```

- `tests/Unit/Dominio`: reglas del dominio puro (totales, promociones, puntos, cobro, corte de caja).
- `tests/Feature/FlujoVentaTest`: flujo completo a través de los casos de uso.
- `tests/Feature/PantallasTest`: todas las pantallas y acciones por HTTP, incluidos los PDF.

Las pruebas usan la base `pos_rincon_test` del mismo contenedor
(`docker exec pos_rincon_postgres psql -U pos_user -d pos_rincon -c "CREATE DATABASE pos_rincon_test"`).
