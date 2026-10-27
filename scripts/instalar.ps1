# Instala el sistema POS en una computadora nueva (Windows / PowerShell).
#
#   powershell -ExecutionPolicy Bypass -File scripts\instalar.ps1          # instalación limpia
#   powershell -ExecutionPolicy Bypass -File scripts\instalar.ps1 -Demo    # además carga ventas de demostración
#
# Requisitos: PHP 8.3 (con pdo_pgsql), Composer y Docker Desktop encendido.
param([switch]$Demo)

$ErrorActionPreference = 'Stop'
Set-Location (Split-Path $PSScriptRoot -Parent)

function Paso($texto) { Write-Host "`n==> $texto" -ForegroundColor Green }
function Falla($texto) { Write-Host "`nERROR: $texto" -ForegroundColor Red; exit 1 }

# Si PHP o Composer no están en el PATH pero Laragon está instalado, se usan los de Laragon.
if (-not (Get-Command php -ErrorAction SilentlyContinue)) {
    $phpLaragon = Get-ChildItem 'C:\laragon\bin\php\php-8*' -Directory -ErrorAction SilentlyContinue | Sort-Object Name -Descending | Select-Object -First 1
    if ($phpLaragon) { $env:Path = "$($phpLaragon.FullName);$env:Path" }
}
if (-not (Get-Command composer -ErrorAction SilentlyContinue) -and (Test-Path 'C:\laragon\bin\composer\composer.bat')) {
    $env:Path = "C:\laragon\bin\composer;$env:Path"
}

# ---------------------------------------------------------------- 1. Herramientas
Paso 'Verificando herramientas'
foreach ($cmd in 'php', 'composer', 'docker') {
    if (-not (Get-Command $cmd -ErrorAction SilentlyContinue)) { Falla "No se encontró '$cmd'. Revise docs/INSTALACION.md (sección Requisitos)." }
}
$version = (php -r 'echo PHP_VERSION;')
if ([version]$version -lt [version]'8.3.0') { Falla "Se necesita PHP 8.3 o superior (encontrado: $version)." }
Write-Host "PHP $version, Composer y Docker encontrados."

# Extensiones que faltan en php.ini (pdo_pgsql y pgsql para PostgreSQL, zip para que Composer descargue paquetes):
# se activan con un .ini adicional que aplica a todo PHP que se ejecute desde este script, incluido Composer.
$faltan = @()
$modulos = (php -m) -join ' '
foreach ($ext in 'pdo_pgsql', 'pgsql', 'zip') { if ($modulos -notmatch "\b$ext\b") { $faltan += $ext } }
if ($faltan.Count -gt 0) {
    $iniDir = Join-Path $env:TEMP 'pos-rincon-php'
    New-Item -ItemType Directory -Force $iniDir | Out-Null
    ($faltan | ForEach-Object { "extension=$_" }) | Set-Content (Join-Path $iniDir 'pos-rincon.ini') -Encoding ascii
    $env:PHP_INI_SCAN_DIR = ";$iniDir"
    $prueba = (php -m) -join ' '
    foreach ($ext in $faltan) {
        if ($prueba -notmatch "\b$ext\b") { Falla "PHP no tiene la extensión $ext. Actívela en php.ini (extension=$ext)." }
    }
    Write-Host "Extensiones activadas solo para este proyecto: $($faltan -join ', ')." -ForegroundColor Yellow
}
function Artisan { php artisan @args; if ($LASTEXITCODE -ne 0) { Falla "Falló: php artisan $args" } }

docker info *> $null
if ($LASTEXITCODE -ne 0) { Falla 'Docker no está encendido. Abra Docker Desktop, espere a que diga "Running" y vuelva a ejecutar.' }

# ---------------------------------------------------------------- 2. Base de datos
Paso 'Levantando PostgreSQL en Docker (puerto 5433)'
docker compose up -d
if ($LASTEXITCODE -ne 0) { Falla 'No se pudo iniciar el contenedor de PostgreSQL.' }
$listo = $false
for ($i = 0; $i -lt 30; $i++) {
    docker exec pos_rincon_postgres pg_isready -U pos_user -d pos_rincon *> $null
    if ($LASTEXITCODE -eq 0) { $listo = $true; break }
    Start-Sleep -Seconds 2
}
if (-not $listo) { Falla 'PostgreSQL no respondió a tiempo. Revise con: docker logs pos_rincon_postgres' }
$existe = docker exec pos_rincon_postgres psql -U pos_user -d pos_rincon -tAc "SELECT 1 FROM pg_database WHERE datname='pos_rincon_test'"
if ($existe -ne '1') { docker exec pos_rincon_postgres psql -U pos_user -d pos_rincon -c 'CREATE DATABASE pos_rincon_test' | Out-Null }
Write-Host 'Base pos_rincon (y pos_rincon_test para las pruebas) lista.'

# ---------------------------------------------------------------- 3. Proyecto Laravel
Paso 'Instalando dependencias de PHP (composer install)'
composer install --no-interaction
if ($LASTEXITCODE -ne 0) { Falla 'composer install falló.' }

if (-not (Test-Path .env)) { Copy-Item .env.example .env; Write-Host 'Se creó el archivo .env a partir de .env.example.' }
if ((Get-Content .env -Raw) -match '(?m)^APP_KEY=\s*$') { Artisan key:generate }

Paso 'Creando las tablas y los datos iniciales'
Artisan migrate:fresh --seed --force
if ($Demo) {
    Paso 'Cargando ventas de demostración'
    Artisan db:seed --class=DemoSeeder --force
}

Paso 'Instalación terminada'
Write-Host 'Inicie el sistema con:  powershell -ExecutionPolicy Bypass -File scripts\iniciar.ps1'
Write-Host 'Usuarios: admin / admin123 (administrador)   cajero / cajero123 (cajero)'
