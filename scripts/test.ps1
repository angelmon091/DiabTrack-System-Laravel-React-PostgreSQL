$ErrorActionPreference = 'Stop'

$php = (Get-Command php).Source
$phpDirectory = Split-Path $php
$extensionDirectory = Join-Path $phpDirectory 'ext'
$pdoSqlite = Join-Path $extensionDirectory 'php_pdo_sqlite.dll'

if (-not (Test-Path $pdoSqlite)) {
    throw "No se encontró php_pdo_sqlite.dll en $extensionDirectory"
}

& $php `
    -d "extension_dir=$extensionDirectory" `
    -d extension=php_pdo_sqlite.dll `
    vendor/bin/phpunit @args

exit $LASTEXITCODE
