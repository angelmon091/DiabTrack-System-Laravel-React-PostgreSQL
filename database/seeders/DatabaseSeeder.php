<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Ejecuta los sembradores principales de la base de datos de la aplicación.
     */
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            SymptomSeeder::class,
        ]);
    }
}
