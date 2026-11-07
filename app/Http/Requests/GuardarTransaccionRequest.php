<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validación del formulario "Nueva Transacción".
 * Antes estaba dentro de store(); aquí queda ordenada y se puede reutilizar.
 */
class GuardarTransaccionRequest extends FormRequest
{
    /**
     * ¿Tiene permiso de hacer esto? Es distinto a si los datos son válidos.
     * Siempre hay usuario: en la web sale de la sesión y en la API del
     * token, porque ambas rutas van detrás de su middleware.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Reglas de validación.
     *
     * El comercio_id no solo debe existir: debe ser del usuario que
     * envía la petición. Filtrar el <select> en la vista no basta,
     * porque el HTML se puede editar desde el navegador.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'comercio_id' => [
                'required',
                Rule::exists('comercios', 'id')
                    ->where('user_id', $this->user()->id),
            ],
            'cliente_nombre' => 'required|string|max:255',

            // max: la columna es decimal(10,2) y sin tope la base
            //   reventaba con un 500 en vez de avisar (E-01).
            // decimal:0,2 acepta de cero a dos decimales; decimal:2
            //   exigiría exactamente dos y rechazaría 25 o 25.5 (E-02).
            'monto' => ['required', 'numeric', 'decimal:0,2', 'min:0.01', 'max:99999999.99'],

            // El estado no se manda al crear: toda transacción nace
            // Iniciada. Antes se ignoraba en silencio (E-03).
            'estado' => ['prohibited'],
        ];
    }

    /**
     * Mensajes propios, en español.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'comercio_id.required' => 'Debes indicar el comercio.',
            'comercio_id.exists' => 'El comercio seleccionado no es válido.',
            'cliente_nombre.required' => 'Debes indicar el nombre del cliente.',
            'monto.required' => 'Debes indicar un monto.',
            'monto.max' => 'El monto no puede superar 99,999,999.99.',
            'monto.decimal' => 'El monto admite como máximo dos decimales.',
            'estado.prohibited' => 'El estado no se envía al crear: toda transacción nace Iniciada.',
            'monto.numeric' => 'El monto debe ser un número.',
            'monto.min' => 'El monto debe ser mayor a cero.',
        ];
    }

    /**
     * Nombres bonitos para los mensajes que no personalicé arriba.
     * Así se lee "comercio" y no "comercio_id".
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'cliente_nombre' => 'nombre del cliente',
            'monto' => 'monto de la transacción',
            'comercio_id' => 'comercio',
        ];
    }
}
