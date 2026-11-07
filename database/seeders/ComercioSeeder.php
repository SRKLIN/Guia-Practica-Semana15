<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class ComercioSeeder extends Seeder
{
    /**
     * Dos usuarios con sus propios comercios, para poder probar el
     * aislamiento: lo de Ana no debe verse desde la cuenta de Carlos.
     *
     * Las contraseñas se cifran solas (cast 'hashed' del modelo User).
     * Los comercios se crean desde ->comercios() para que el user_id
     * lo ponga Eloquent y nunca quede un comercio sin dueño.
     */
    public function run(): void
    {
        $ana = User::create([
            'name' => 'Ana López',
            'email' => 'ana@taskboard.test',
            'password' => 'password123',
        ]);

        $carlos = User::create([
            'name' => 'Carlos Pérez',
            'email' => 'carlos@taskboard.test',
            'password' => 'password123',
        ]);

        // Comercio de Ana, con dos transacciones
        $cafeAmanecer = $ana->comercios()->create([
            'nombre_comercio' => 'Café Amanecer',
            'rubro' => 'Restaurante',
            'telefono' => '2222-1234',
        ]);

        $cafeAmanecer->transacciones()->createMany([
            [
                'monto' => 45.00,
                'cliente_nombre' => 'María López',
                'estado' => 'Completada',
            ],
            [
                'monto' => 12.50,
                'cliente_nombre' => 'Juan Pérez',
                'estado' => 'Iniciada',
            ],
        ]);

        // Comercio de Carlos sin transacciones, para probar @empty
        $carlos->comercios()->create([
            'nombre_comercio' => 'Ferretería El Tornillo',
            'rubro' => 'Ferretería',
            'telefono' => '2222-5678',
        ]);

        // Segundo comercio de Ana: las guías de la Semana 14 cuentan
        // 2 comercios para Ana y 1 para Carlos.
        $pupuseria = $ana->comercios()->create([
            'nombre_comercio' => 'Pupusería Doña Marta',
            'rubro' => 'Restaurante',
            'telefono' => '2222-9012',
        ]);

        $pupuseria->transacciones()->create([
            'monto' => 8.00,
            'cliente_nombre' => 'Carlos Ramírez',
            'estado' => 'Fallida',
        ]);
    }
}
