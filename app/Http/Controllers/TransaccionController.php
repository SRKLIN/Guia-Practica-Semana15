<?php

namespace App\Http\Controllers;

use App\Http\Requests\GuardarTransaccionRequest;
use App\Models\Comercio;
use App\Models\Transaccion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TransaccionController extends Controller
{
    /**
     * Una transacción es tuya si su comercio lo es.
     * Se responde 404 para no confirmar siquiera que existe.
     */
    private function autorizar(Transaccion $transaccion): void
    {
        abort_unless($transaccion->comercio->user_id === Auth::id(), 404);
    }

    /**
     * Muestra el formulario de nueva transacción.
     * {comercio} llega ya convertido en instancia del modelo.
     */
    public function create(Comercio $comercio): View
    {
        abort_unless($comercio->user_id === Auth::id(), 404);

        return view('transacciones.create', compact('comercio'));
    }

    /**
     * Guarda la transacción y vuelve al comercio con un mensaje.
     *
     * El type-hint GuardarTransaccionRequest valida antes de entrar
     * aquí, por eso ya no hay validate() en este método.
     *
     * only() toma solo esos 3 campos: si alguien inyecta un "estado"
     * desde el navegador, se ignora. Con all() sí habría pasado.
     *
     * Se redirige en lugar de devolver la vista para que F5 no duplique.
     */
    public function store(GuardarTransaccionRequest $request): RedirectResponse
    {
        $transaccion = Transaccion::create($request->only([
            'comercio_id', 'cliente_nombre', 'monto',
        ]));

        return redirect()
            ->route('comercios.show', $transaccion->comercio_id)
            ->with('mensaje', 'Transacción registrada con éxito.');
    }

    /**
     * Muestra el formulario de edición, ya pre-llenado.
     *
     * No hay ningún Transaccion::find() aquí: Route Model Binding
     * resuelve {transaccion} antes de entrar al método, y si el id no
     * existe lanza el 404 por su cuenta.
     */
    public function edit(Transaccion $transaccion): View
    {
        $this->autorizar($transaccion);

        // Solo los comercios propios pueden salir en el select
        $comercios = Auth::user()->comercios()->orderBy('nombre_comercio')->get();

        return view('transacciones.edit', compact('transaccion', 'comercios'));
    }

    /**
     * Guarda los cambios. Reutiliza el mismo Form Request de store()
     * para no mantener dos copias de las mismas reglas.
     */
    public function update(
        GuardarTransaccionRequest $request,
        Transaccion $transaccion
    ): RedirectResponse {
        $this->autorizar($transaccion);

        $transaccion->update($request->validated());

        return redirect()
            ->route('comercios.show', $transaccion->comercio_id)
            ->with('mensaje', 'Transacción actualizada correctamente.');
    }

    /**
     * Elimina la transacción. La confirmación vive en la vista, con un
     * confirm() en el onsubmit del formulario.
     *
     * El comercio y el cliente se guardan ANTES del delete(), porque
     * después el modelo ya no sirve para leer sus datos.
     */
    public function destroy(Transaccion $transaccion): RedirectResponse
    {
        $this->autorizar($transaccion);

        $comercioId = $transaccion->comercio_id;
        $cliente = $transaccion->cliente_nombre;

        $transaccion->delete();

        return redirect()
            ->route('comercios.show', $comercioId)
            ->with('mensaje', "Transacción de {$cliente} eliminada.");
    }

    /**
     * Cambia solo el estado, sin pasar por el formulario completo.
     *
     * Dos revisiones distintas: que el estado exista, y que la
     * transición desde el estado actual tenga sentido de negocio.
     * Una transacción Completada ya movió dinero: no se revierte
     * con un clic.
     */
    public function moverEstado(Transaccion $transaccion, string $estado): RedirectResponse
    {
        $this->autorizar($transaccion);

        $validos = ['Iniciada', 'Completada', 'Fallida'];

        if (! in_array($estado, $validos)) {
            abort(400, 'Estado no válido.');
        }

        $transiciones = [
            'Iniciada' => ['Completada', 'Fallida'],
            'Completada' => [],
            'Fallida' => ['Iniciada'],
        ];

        if (! in_array($estado, $transiciones[$transaccion->estado] ?? [])) {
            abort(400, "No se puede mover de {$transaccion->estado} a {$estado}.");
        }

        $transaccion->update(['estado' => $estado]);

        return redirect()
            ->route('comercios.show', $transaccion->comercio_id)
            ->with('mensaje', "Transacción movida a {$estado}.");
    }

    /*
     * Reto: el mismo Form Request en otro método, sin repetir reglas.
     * validated() devuelve solo los campos que pasaron por rules().
     * Ya probado; se deja comentado como pide la guía.
     *
     * public function duplicarUltima(GuardarTransaccionRequest $request)
     * {
     *     dd($request->validated());
     * }
     */
}
