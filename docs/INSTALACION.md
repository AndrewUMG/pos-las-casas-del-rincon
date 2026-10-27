# Instalar el sistema en otra computadora

Guía paso a paso para montar el **Sistema POS e Inventario – Las Casas del Rincón Guatemalteco** en cualquier
computadora con Windows. Tiempo aproximado: 15 minutos (la primera vez Docker descarga la imagen de PostgreSQL).

## 1. Requisitos

| Programa | Para qué sirve | Dónde obtenerlo |
|----------|----------------|-----------------|
| **Git** | Descargar el código | <https://git-scm.com/download/win> |
| **Laragon** (incluye PHP 8.3 y Composer) | Ejecutar Laravel | <https://laragon.org/download/> |
| **Docker Desktop** | Ejecutar la base de datos PostgreSQL 16 sin instalarla | <https://www.docker.com/products/docker-desktop/> |

> Si ya tiene PHP 8.3 y Composer instalados por su cuenta, no necesita Laragon.
> Si prefiere no usar Docker, vea la sección [Sin Docker](#7-sin-docker-postgresql-instalado-en-windows).

Antes de continuar, **abra Docker Desktop** y espere a que en la parte inferior diga *Engine running*.

## 2. Descargar el proyecto

Abra **PowerShell** y ejecute:

```powershell
cd $HOME\Desktop
git clone https://github.com/AndrewUMG/pos-las-casas-del-rincon.git
cd pos-las-casas-del-rincon
```

## 3. Instalar (un solo comando)

```powershell
powershell -ExecutionPolicy Bypass -File scripts\instalar.ps1 -Demo
```

El script hace todo por usted:

1. Verifica PHP 8.3, Composer y Docker (si usa Laragon, los encuentra solo aunque no estén en el PATH).
2. Activa las extensiones `pdo_pgsql` y `pgsql` de PHP para el proyecto si no lo están.
3. Levanta PostgreSQL en Docker en el puerto **5433** y crea la base de pruebas.
4. Ejecuta `composer install`, crea el archivo `.env` y la clave de la aplicación.
5. Crea las tablas y los datos iniciales (familias, proveedores, 23 productos, clientes, promociones y usuarios).
6. Con `-Demo`, además registra ventas de ejemplo: una compra, la caja abierta, tres facturas (una con canje de
   cupón QR), una devolución y una venta en proceso. **Recomendado para presentar.**

Sin `-Demo` el sistema queda limpio, solo con los catálogos.

## 4. Iniciar el sistema

```powershell
powershell -ExecutionPolicy Bypass -File scripts\iniciar.ps1
```

Se abre el navegador en <http://127.0.0.1:8000>. Para detenerlo presione **Ctrl + C** en PowerShell.

| Usuario | Contraseña | Rol | Ve en el menú |
|---------|------------|-----|---------------|
| `admin` | `admin123` | Administrador | Todos los módulos |
| `cajero` | `cajero123` | Cajero | Inicio, Punto de venta, Caja y Devoluciones |

### Leer el código QR de la factura con el celular

```powershell
powershell -ExecutionPolicy Bypass -File scripts\iniciar.ps1 -Red
```

Con `-Red` el sistema se publica en la IP de la computadora (por ejemplo `http://192.168.1.50:8000`) y las facturas
nuevas generan el QR con esa dirección. El celular debe estar **en la misma red Wi-Fi**. Si Windows pregunta por el
firewall, permita el acceso en redes privadas.

> Los QR de facturas emitidas antes de usar `-Red` apuntan a `127.0.0.1` y no abren en el celular:
> emita una factura nueva después de iniciar con `-Red`.

## 5. Volver a dejar los datos como al inicio

```powershell
powershell -ExecutionPolicy Bypass -File scripts\instalar.ps1 -Demo
```

Borra todo y vuelve a crear los datos iniciales y de demostración (útil antes de cada presentación).

## 6. Ejecutar las pruebas automáticas

```powershell
php -d extension=pdo_pgsql -d extension=pgsql vendor\phpunit\phpunit\phpunit
```

Resultado esperado: **17 pruebas, 147 verificaciones, todas en verde**. Las pruebas usan la base `pos_rincon_test`,
así que no tocan sus datos. (Si las extensiones ya están activas en `php.ini`, basta con `php artisan test`.)

## 7. Sin Docker (PostgreSQL instalado en Windows)

1. Instale PostgreSQL 16 desde <https://www.postgresql.org/download/windows/>.
2. Con pgAdmin o `psql`, cree el usuario y las bases:
   ```sql
   CREATE USER pos_user WITH PASSWORD 'pos_secret';
   CREATE DATABASE pos_rincon OWNER pos_user;
   CREATE DATABASE pos_rincon_test OWNER pos_user;
   ```
3. Copie `.env.example` a `.env` y cambie `DB_PORT=5433` por `DB_PORT=5432`.
4. Ejecute:
   ```powershell
   composer install
   php artisan key:generate
   php artisan migrate:fresh --seed
   php artisan db:seed --class=DemoSeeder
   php artisan serve
   ```
   (En Laragon active antes **Menú › PHP › Extensions › pdo_pgsql y pgsql**.)

## 8. Problemas frecuentes

| Mensaje | Causa | Solución |
|---------|-------|----------|
| `No se encontró 'php'` | PHP no está instalado o no está en el PATH | Instale Laragon en `C:\laragon` o agregue PHP al PATH |
| `Docker no está encendido` | Docker Desktop cerrado | Ábralo y espere a *Engine running* |
| `could not find driver` o `The zip extension and unzip/7z commands are both missing` | Falta una extensión de PHP (`pdo_pgsql`, `pgsql` o `zip`) | Use los scripts (las activan solos) o actívelas en `php.ini` |
| `port is already allocated` (5433) | Otro PostgreSQL usa el puerto | Cambie `5433` en `docker-compose.yml` y en `DB_PORT` del `.env` |
| `Address already in use` (8000) | Otro programa usa el puerto 8000 | `scripts\iniciar.ps1 -Puerto 8080` |
| La ejecución de scripts está deshabilitada | Política de PowerShell | Use siempre `powershell -ExecutionPolicy Bypass -File ...` |
| El celular no abre el QR | Distinta red o firewall | Misma Wi-Fi, permitir PHP en el firewall y emitir una factura nueva con `-Red` |

## 9. Guion para presentar

Vea [GUION_DEMO.md](GUION_DEMO.md): recorrido de 10 minutos por todos los requerimientos del proyecto.
