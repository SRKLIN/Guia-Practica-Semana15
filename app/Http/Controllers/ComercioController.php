<?php

namespace App\Http\Controllers;

use App\Models\Comercio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ComercioController extends Controller
{
    /**
     * Semana 5 · Routing y Controladores
     * GET /comercios
     *
     * Lista los comercios del usuario con sesión iniciada, junto con el
     * conteo de sus transacciones. withCount() evita el problema N+1.
     *
     * La consulta arranca en Auth::user()->comercios(), no en
     * Comercio::query(): así el filtro por dueño no se puede saltar.
     *
     * Filtra por GET: buscar (nombre) y rubro. when() solo aplica el
     * where si el campo trae valor, así que vacíos muestran todo.
     */
    public function index(Request $request): View
    {
        $comercios = Auth::user()->comercios()
            ->when($request->buscar,
                fn ($q) => $q->where('nombre_comercio',
                    'like', "%{$request->buscar}%"))
            ->when($request->rubro,
                fn ($q) => $q->where('rubro', $request->rubro))
            ->withCount('transacciones')
            ->orderBy('nombre_comercio')
            ->get();

        // Llena el <select> de rubros, solo con los rubros propios
        $rubros = Auth::user()->comercios()
            ->select('rubro')
            ->distinct()
            ->orderBy('rubro')
            ->pluck('rubro');

        return view('comercios.index', compact('comercios', 'rubros'));
    }

    /**
     * GET /comercios/{comercio}
     *
     * Route Model Binding: Laravel convierte automáticamente el
     * parámetro {comercio} de la ruta en una instancia de Comercio
     * (o lanza 404 si no existe).
     *
     * Listar solo lo propio no basta: alguien puede escribir el id de
     * otro en la URL. Por eso se compara el dueño contra Auth::id() y
     * se responde 404, que ni siquiera confirma que ese comercio exista.
     *
     * load('transacciones') trae la relación en una sola consulta
     * adicional (no una por cada transacción), evitando el N+1.
     */
    public function show(Comercio $comercio): View
    {
        abort_unless($comercio->user_id === Auth::id(), 404);

        $comercio->load('transacciones');

        return view('comercios.show', compact('comercio'));
    }
}
