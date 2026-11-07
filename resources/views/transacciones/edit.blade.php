{{-- transacciones/edit.blade.php --}}
@extends('layouts.app')
@section('titulo', 'Editar transacción')

@section('contenido')
    <p><a href="{{ route('comercios.show', $transaccion->comercio_id) }}">&larr; Volver al comercio</a></p>

    <h1>Editar transacción</h1>

    {{-- Lista todos los errores juntos --}}
    @if ($errors->any())
        <div class="resumen-errores">
            <strong>Revisa estos datos antes de continuar:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Va por POST pero con @method('PUT'): un formulario HTML solo
         entiende GET y POST, Laravel lee el verbo de un campo oculto --}}
    <form action="{{ route('transacciones.update', $transaccion) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- old() con dos argumentos: primero lo que el usuario acaba de
             escribir si hubo error, y si no hay nada, el valor guardado --}}
        <label for="comercio">Comercio</label>
        <select id="comercio" name="comercio_id">
            @foreach ($comercios as $c)
                <option value="{{ $c->id }}"
                    @selected(old('comercio_id', $transaccion->comercio_id) == $c->id)>
                    {{ $c->nombre_comercio }}
                </option>
            @endforeach
        </select>
        @error('comercio_id')
            <span class="error">{{ $message }}</span>
        @enderror
        <br>

        <label for="cliente">Cliente</label>
        <input id="cliente" name="cliente_nombre" type="text"
               value="{{ old('cliente_nombre', $transaccion->cliente_nombre) }}">
        @error('cliente_nombre')
            <span class="error">{{ $message }}</span>
        @enderror
        <br>

        <label for="monto">Monto</label>
        <input id="monto" name="monto" type="number" step="0.01"
               value="{{ old('monto', $transaccion->monto) }}">
        @error('monto')
            <span class="error">{{ $message }}</span>
        @enderror
        <br>

        <button type="submit">Guardar cambios</button>
    </form>
@endsection
