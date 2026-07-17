<?php

namespace App\Console\Commands;

use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use Illuminate\Console\Command;

class StressTestCommand extends Command
{
    /**
     * Nombre y firma utilizados para ejecutar el comando desde Artisan.
     *
     * @var string
     */
    protected $signature = 'app:stress-test {--requests=1000 : Total number of requests} {--concurrency=100 : Number of concurrent requests}';

    /**
     * Descripción breve que se muestra en la ayuda de Artisan.
     *
     * @var string
     */
    protected $description = 'Run a professional stress test on the application local endpoint';

    /**
     * Ejecuta la prueba de carga configurada para el sistema.
     */
    public function handle()
    {
        $totalRequests = (int) $this->option('requests');
        $concurrency = (int) $this->option('concurrency');

        $this->info("Starting Stress Test: $totalRequests total requests with $concurrency concurrency");
        $this->warn('Target: http://localhost:8000');

        $client = new Client([
            'base_uri' => 'http://localhost:8000',
            'timeout' => 15.0,
            'http_errors' => false,
        ]);

        $start = microtime(true);
        $success = 0;
        $failed = 0;

        $bar = $this->output->createProgressBar($totalRequests);
        $bar->start();

        // Procesa por lotes para limitar el uso de memoria en pruebas extensas con Guzzle.
        $batches = ceil($totalRequests / $concurrency);

        for ($b = 0; $b < $batches; $b++) {
            $promises = [];
            $currentBatchSize = min($concurrency, $totalRequests - ($b * $concurrency));

            for ($i = 0; $i < $currentBatchSize; $i++) {
                $promises[] = $client->getAsync('/');
            }

            $results = Promise\Utils::settle($promises)->wait();

            foreach ($results as $result) {
                if ($result['state'] === 'fulfilled' && $result['value']->getStatusCode() < 400) {
                    $success++;
                } else {
                    $failed++;
                }
                $bar->advance();
            }
        }

        $bar->finish();
        $end = microtime(true);
        $duration = $end - $start;

        $this->newLine(2);
        $this->table(
            ['Metric', 'Value'],
            [
                ['Duration', round($duration, 2).' seconds'],
                ['Requests per second', round($totalRequests / $duration, 2)],
                ['Successful', $success],
                ['Failed', $failed],
            ]
        );

        if ($failed === 0) {
            $this->info('Test completed successfully! The infrastructure is robust.');
        } else {
            $this->error("Test finished with $failed errors. Check server logs.");
        }
    }
}
