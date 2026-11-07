<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GuardarTransaccionRequest;
use App\Http\Resources\TransaccionResource;
use App\Models\Transaccion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

/**
 * Controlador de la API: devuelve JSON, nunca vistas.
 */
class TransaccionApiController extends Controller
{
    /**
     * Una transaccion es del usuario si su comercio lo es.
     * Se responde 404 para no confirmar siquiera que existe.
     */
    private function autorizar(Transaccion $transaccion): void
    {
        abort_unless($transaccion->comercio->user_id === auth()->id(), 404);
    }

    /**
     * Lista las transacciones de los comercios del dueno del token,
     * de la mas nueva a la mas vieja.
     * ?estado=Completada filtra; sin el parametro salen todas.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $estado = $request->query('estado');

        $transacciones = Transaccion::whereHas('comercio',
                fn ($q) => $q->where('user_id', $request->user()->id))
            ->when($estado, fn ($q) => $q->where('estado', $estado))
            ->latest()
            ->get();

        return TransaccionResource::collection($transacciones);
    }

    /**
     * Crea una transacción. 201 Created con el objeto creado.
     *
     * Reutiliza el mismo Form Request del formulario web: las reglas
     * y los mensajes viven en un solo lugar. Si falla, Laravel responde
     * 422 con los errores en JSON (si la petición manda Accept: application/json).
     */
    public function store(GuardarTransaccionRequest $request): JsonResponse
    {
        // Todo o nada: son dos escrituras (la transacción y su evento).
        // Si la segunda falla, DB::transaction deshace también la
        // primera, y no queda una transacción sin bitácora.
        $transaccion = DB::transaction(function () use ($request) {
            $transaccion = Transaccion::create($request->validated());

            $transaccion->eventos()->create([
                'tipo_evento' => 'creacion',
                'detalle' => 'Transacción iniciada',
            ]);

            return $transaccion;
        });

        // refresh() relee la fila: sin esto, 'estado' saldría null en la
        // respuesta, porque el valor lo pone el default de la columna y
        // el objeto en memoria no se enteró.
        $transaccion->refresh();

        return (new TransaccionResource($transaccion))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Una transacción. Si el id no existe, Route Model Binding da 404.
     */
    public function show(Transaccion $transaccion): TransaccionResource
    {
        $this->autorizar($transaccion);

        return new TransaccionResource($transaccion);
    }

    /**
     * Actualiza y devuelve el objeto ya modificado.
     */
    public function update(
        GuardarTransaccionRequest $request,
        Transaccion $transaccion
    ): TransaccionResource {
        $this->autorizar($transaccion);

        $transaccion->update($request->validated());

        return new TransaccionResource($transaccion);
    }

    /**
     * Elimina. 204 No Content: se borró y no hay nada que devolver.
     */
    public function destroy(Transaccion $transaccion): Response
    {
        $this->autorizar($transaccion);

        $transaccion->delete();

        return response()->noContent();
    }
}
