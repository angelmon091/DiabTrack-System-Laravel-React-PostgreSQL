<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        $environment = $this->environmentValue('APP_ENV');
        $connection = $this->environmentValue('DB_CONNECTION');
        $database = $this->environmentValue('DB_DATABASE');

        if ($environment !== 'testing' || $connection !== 'sqlite' || $database !== ':memory:') {
            throw new RuntimeException(sprintf(
                'Ejecucion bloqueada: PHPUnit solo puede usar APP_ENV=testing y SQLite :memory:. Valores detectados: APP_ENV=%s, DB_CONNECTION=%s, DB_DATABASE=%s.',
                $environment ?: '(vacio)',
                $connection ?: '(vacio)',
                $database ?: '(vacio)',
            ));
        }

        $cachedConfigPath = dirname(__DIR__).'/bootstrap/cache/config.php';

        if (is_file($cachedConfigPath)) {
            $cachedConfig = require $cachedConfigPath;
            $cachedConnection = $cachedConfig['database']['default'] ?? null;
            $cachedDatabase = $cachedConfig['database']['connections']['sqlite']['database'] ?? null;

            if ($cachedConnection !== 'sqlite' || $cachedDatabase !== ':memory:') {
                throw new RuntimeException(
                    'Ejecucion bloqueada antes de iniciar Laravel: existe una configuracion cacheada que no utiliza SQLite :memory:. Ejecute php artisan config:clear.'
                );
            }
        }

        parent::setUp();

        if (app()->environment() !== 'testing'
            || config('database.default') !== 'sqlite'
            || config('database.connections.sqlite.database') !== ':memory:') {
            throw new RuntimeException('Ejecucion bloqueada: la configuracion efectiva de pruebas no utiliza SQLite :memory:.');
        }
    }

    private function environmentValue(string $key): string
    {
        $value = $_SERVER[$key] ?? $_ENV[$key] ?? getenv($key);

        return is_string($value) ? $value : '';
    }
}
