<?php

namespace Tests\Feature;

use App\Models\Transaccion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Pruebas de regresión: cada una corresponde a un error de la bitácora.
 * Si alguien quita la regla que lo corrigió, la prueba se pone en rojo.
 */
class CrearTransaccionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Siembra la base, entra como Ana y manda un POST a la API.
     * Los $datos que se pasen reemplazan a los de ejemplo.
     */
    private function crear(array $datos)
    {
        $this->seed();
        Sanctum::actingAs(User::where('email', 'ana@taskboard.test')->first());

        return $this->postJson('/api/transacciones', array_merge([
            'comercio_id' => 1,
            'cliente_nombre' => 'Luis Mena',
            'monto' => 25.50,
        ], $datos));
    }

    public function test_cl02_monto_cero_responde_422(): void
    {
        $this->crear(['monto' => 0])
            ->assertStatus(422)
            ->assertJsonValidationErrors('monto');
    }

    /** E-01: antes daba 500 porque la columna es decimal(10,2) */
    public function test_e01_monto_sobre_el_maximo(): void
    {
        $this->crear(['monto' => 100000000])
            ->assertStatus(422)
            ->assertJsonValidationErrors('monto');
    }

    /** E-02: antes aceptaba tres decimales y los redondeaba en silencio */
    public function test_e02_tres_decimales(): void
    {
        $this->crear(['monto' => 25.999])
            ->assertStatus(422)
            ->assertJsonValidationErrors('monto');
    }

    /** E-03: antes ignoraba el estado enviado, sin avisar */
    public function test_e03_estado_prohibido(): void
    {
        $this->crear(['estado' => 'Completada'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('estado');
    }

    /** El limite de abajo debe seguir funcionando */
    public function test_cl01_monto_minimo_se_acepta(): void
    {
        $this->crear(['monto' => 0.01])->assertStatus(201);
    }

    /** Lab 5: la transaccion y su evento se guardan juntos */
    public function test_crear_registra_el_evento(): void
    {
        $this->crear([])->assertStatus(201);

        $id = Transaccion::latest('id')->first()->id;

        $this->assertDatabaseHas('eventos_transaccion', [
            'transaccion_id' => $id,
            'tipo_evento' => 'creacion',
        ]);
    }
}
