<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validación del formulario de registro.
 */
class RegistroRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * confirmed obliga a que exista un campo password_confirmation
     * con el mismo valor.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'min:8', 'confirmed'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Debes indicar tu nombre.',
            'email.required' => 'Debes indicar tu correo.',
            'email.email' => 'El correo no tiene un formato válido.',
            'email.unique' => 'Ese correo ya está registrado.',
            'password.required' => 'Debes indicar una contraseña.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ];
    }
}
