$ErrorActionPreference = 'Stop'

# PHPUnit nunca debe heredar la base local ni la base de producción.
$env:APP_ENV = 'testing'
$env:APP_LOCALE = 'es'
$env:APP_FALLBACK_LOCALE = 'es'
$env:DB_CONNECTION = 'sqlite'
$env:DB_DATABASE = ':memory:'
$env:DB_URL = ''
$env:CACHE_STORE = 'array'
$env:SESSION_DRIVER = 'array'
$env:QUEUE_CONNECTION = 'sync'
$env:MAIL_MAILER = 'array'

$php = (Get-Command php).Source
$phpDirectory = Split-Path $php
$extensionDirectory = Join-Path $phpDirectory 'ext'
$pdoSqlite = Join-Path $extensionDirectory 'php_pdo_sqlite.dll'

if (-not (Test-Path $pdoSqlite)) {
    throw "No se encontró php_pdo_sqlite.dll en $extensionDirectory"
}

& $php artisan config:clear --no-ansi
& $php artisan route:clear --no-ansi
& $php artisan view:clear --no-ansi

if ($LASTEXITCODE -ne 0) {
    throw 'No se pudieron limpiar las caches antes de PHPUnit.'
}

& $php `
    -d "extension_dir=$extensionDirectory" `
    -d extension=php_pdo_sqlite.dll `
    vendor/bin/phpunit @args

exit $LASTEXITCODE
