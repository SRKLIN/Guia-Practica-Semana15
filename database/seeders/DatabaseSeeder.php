<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Semana 6 · Eloquent ORM
     * Punto de entrada de todos los seeders del proyecto.
     */
    public function run(): void
    {
        $this->call([
            ComercioSeeder::class,
        ]);
    }
}
