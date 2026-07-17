$ErrorActionPreference = 'Stop'

$php = (Get-Command php).Source
$phpDirectory = Split-Path $php
$extensionDirectory = Join-Path $phpDirectory 'ext'
$database = Join-Path $PSScriptRoot '..\database\dusk.sqlite'
$duskIni = Join-Path $env:TEMP "diabtrack-dusk-$PID.ini"

@"
extension_dir="$extensionDirectory"
extension=php_pdo_sqlite.dll
extension=php_mbstring.dll
extension=php_openssl.dll
extension=php_curl.dll
extension=php_fileinfo.dll
date.timezone=America/Mexico_City
"@ | Set-Content -LiteralPath $duskIni -Encoding ascii

if (-not (Test-Path $database)) {
    New-Item -ItemType File -Path $database | Out-Null
}

$existingServer = Get-NetTCPConnection -LocalPort 8000 -State Listen -ErrorAction SilentlyContinue
if ($existingServer) {
    Stop-Process -Id $existingServer.OwningProcess -Force
}

$env:APP_ENV = 'dusk'
$env:APP_URL = 'http://127.0.0.1:8000'
$env:DB_CONNECTION = 'sqlite'
$env:DB_DATABASE = (Resolve-Path $database).Path
$env:DB_URL = ''
$env:CACHE_STORE = 'array'
$env:SESSION_DRIVER = 'file'
$env:QUEUE_CONNECTION = 'sync'
$env:MAIL_MAILER = 'array'
$env:PHPRC = (Resolve-Path $duskIni).Path

# Evita que una configuración local cacheada redirija Dusk hacia MySQL.
& $php -c $duskIni artisan config:clear
if ($LASTEXITCODE -ne 0) {
    throw 'No fue posible limpiar la configuración antes de ejecutar Dusk.'
}

$arguments = @(
    '-c', $duskIni,
    '-S', '127.0.0.1:8000',
    '-t', '.',
    'index.php'
)

$serverLog = Join-Path $PSScriptRoot '..\storage\logs\dusk-server.log'
$serverErrorLog = Join-Path $PSScriptRoot '..\storage\logs\dusk-server-error.log'
$server = Start-Process -FilePath $php -ArgumentList $arguments -PassThru -WindowStyle Hidden `
    -WorkingDirectory (Join-Path $PSScriptRoot '..\public') `
    -RedirectStandardOutput $serverLog -RedirectStandardError $serverErrorLog

try {
    $ready = $false
    for ($attempt = 0; $attempt -lt 30; $attempt++) {
        try {
            Invoke-WebRequest -Uri $env:APP_URL -UseBasicParsing -TimeoutSec 2 | Out-Null
            $ready = $true
            break
        } catch {
            Start-Sleep -Milliseconds 500
        }
    }

    if (-not $ready) {
        throw 'El servidor local de Dusk no respondió en el tiempo esperado.'
    }

    & $php -c $duskIni artisan dusk @args
    $testExitCode = $LASTEXITCODE
} finally {
    if (-not $server.HasExited) {
        Stop-Process -Id $server.Id -Force
    }

    $listener = Get-NetTCPConnection -LocalPort 8000 -State Listen -ErrorAction SilentlyContinue
    if ($listener) {
        Stop-Process -Id $listener.OwningProcess -Force
    }

    Remove-Item -LiteralPath $duskIni -Force -ErrorAction SilentlyContinue
}

exit $testExitCode
