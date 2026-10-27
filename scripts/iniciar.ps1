# Inicia el sistema POS ya instalado (Windows / PowerShell).
#
#   powershell -ExecutionPolicy Bypass -File scripts\iniciar.ps1        # solo en esta computadora
#   powershell -ExecutionPolicy Bypass -File scripts\iniciar.ps1 -Red   # accesible desde el celular (para leer el QR)
param([switch]$Red, [int]$Puerto = 8000)

$ErrorActionPreference = 'Stop'
Set-Location (Split-Path $PSScriptRoot -Parent)

# Si PHP no está en el PATH pero Laragon está instalado, se usa el de Laragon.
if (-not (Get-Command php -ErrorAction SilentlyContinue)) {
    $phpLaragon = Get-ChildItem 'C:\laragon\bin\php\php-8*' -Directory -ErrorAction SilentlyContinue | Sort-Object Name -Descending | Select-Object -First 1
    if ($phpLaragon) { $env:Path = "$($phpLaragon.FullName);$env:Path" }
}

docker info *> $null
if ($LASTEXITCODE -ne 0) { Write-Host 'Docker no está encendido. Abra Docker Desktop y vuelva a ejecutar.' -ForegroundColor Red; exit 1 }
docker compose up -d | Out-Null
for ($i = 0; $i -lt 30; $i++) {
    docker exec pos_rincon_postgres pg_isready -U pos_user -d pos_rincon *> $null
    if ($LASTEXITCODE -eq 0) { break }
    Start-Sleep -Seconds 2
}

$PhpArgs = @()
$modulos = (php -m) -join ' '
foreach ($ext in 'pdo_pgsql', 'pgsql') { if ($modulos -notmatch "\b$ext\b") { $PhpArgs += @('-d', "extension=$ext") } }

$hostServidor = '127.0.0.1'
$url = "http://127.0.0.1:$Puerto"
if ($Red) {
    # La URL del QR debe apuntar a la IP de esta computadora para que el celular (en la misma red Wi-Fi) la abra.
    $ip = (Get-NetIPAddress -AddressFamily IPv4 | Where-Object { $_.IPAddress -notmatch '^(127\.|169\.254\.)' -and $_.PrefixOrigin -ne 'WellKnown' } |
        Sort-Object InterfaceMetric | Select-Object -First 1).IPAddress
    if (-not $ip) { Write-Host 'No se encontró una IP de red; se usará solo localhost.' -ForegroundColor Yellow }
    else { $hostServidor = '0.0.0.0'; $url = "http://${ip}:$Puerto" }
}
$envTexto = (Get-Content .env) -replace '^APP_URL=.*', "APP_URL=$url"
[IO.File]::WriteAllLines((Join-Path (Get-Location) '.env'), $envTexto, (New-Object Text.UTF8Encoding $false))
php @PhpArgs artisan config:clear | Out-Null

Write-Host "`nSistema POS en $url" -ForegroundColor Green
Write-Host 'Usuarios: admin / admin123   cajero / cajero123'
Write-Host 'Para detenerlo presione Ctrl + C.'
Start-Process $url
# Se usa el servidor integrado de PHP para que las extensiones activadas arriba también apliquen a cada petición.
Set-Location public
php @PhpArgs -S "${hostServidor}:$Puerto" ../vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php
