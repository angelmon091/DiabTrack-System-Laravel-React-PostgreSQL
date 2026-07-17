<?php

namespace Tests;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Support\Collection;
use Laravel\Dusk\TestCase as BaseTestCase;
use PHPUnit\Framework\Attributes\BeforeClass;
use RuntimeException;

abstract class DuskTestCase extends BaseTestCase
{
    /**
     * Verifica que Dusk permanezca aislado en su base SQLite antes de abrir el navegador.
     */
    protected function setUp(): void
    {
        $environment = $_SERVER['APP_ENV'] ?? $_ENV['APP_ENV'] ?? getenv('APP_ENV');
        $connection = $_SERVER['DB_CONNECTION'] ?? $_ENV['DB_CONNECTION'] ?? getenv('DB_CONNECTION');
        $database = $_SERVER['DB_DATABASE'] ?? $_ENV['DB_DATABASE'] ?? getenv('DB_DATABASE');

        if ($environment !== 'dusk'
            || $connection !== 'sqlite'
            || ! is_string($database)
            || basename($database) !== 'dusk.sqlite') {
            throw new RuntimeException('Ejecución bloqueada: Dusk debe utilizar APP_ENV=dusk y database/dusk.sqlite.');
        }

        parent::setUp();

        if (config('database.default') !== 'sqlite'
            || basename((string) config('database.connections.sqlite.database')) !== 'dusk.sqlite') {
            throw new RuntimeException('Ejecución bloqueada: la configuración efectiva de Dusk no utiliza su base SQLite aislada.');
        }
    }

    /**
     * Prepara el entorno necesario para ejecutar las pruebas de navegador con Dusk.
     */
    #[BeforeClass]
    public static function prepare(): void
    {
        if (! static::runningInSail()) {
            static::startChromeDriver(['--port=9515']);
        }
    }

    /**
     * Crea la instancia del controlador remoto utilizada por el navegador.
     */
    protected function driver(): RemoteWebDriver
    {
        $options = (new ChromeOptions)->addArguments(collect([
            $this->shouldStartMaximized() ? '--start-maximized' : '--window-size=1920,1080',
            '--disable-search-engine-choice-screen',
            '--disable-smooth-scrolling',
        ])->unless($this->hasHeadlessDisabled(), function (Collection $items) {
            return $items->merge([
                '--disable-gpu',
                '--headless=new',
            ]);
        })->all());

        return RemoteWebDriver::create(
            $_ENV['DUSK_DRIVER_URL'] ?? env('DUSK_DRIVER_URL') ?? 'http://localhost:9515',
            DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY, $options
            )
        );
    }
}
