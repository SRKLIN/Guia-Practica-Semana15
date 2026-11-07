<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Login, identidad y logout de la API, con tokens de Sanctum.
 */
class AuthApiController extends Controller
{
    /**
     * Verifica las credenciales y entrega un token personal.
     *
     * El mensaje de error es genérico a propósito: decir "ese correo no
     * existe" le confirmaría a un atacante qué cuentas hay.
     */
    public function login(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
            'dispositivo' => ['required', 'max:100'],
        ]);

        $user = User::where('email', $datos['email'])->first();

        if (! $user || ! Hash::check($datos['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => 'Las credenciales no coinciden.',
            ]);
        }

        $token = $user->createToken($datos['dispositivo']);

        // plainTextToken solo existe en esta respuesta: en la base se
        // guarda su hash, así que después ya no se puede recuperar.
        return response()->json([
            'token' => $token->plainTextToken,
            'tipo' => 'Bearer',
        ], 201);
    }

    /**
     * Quién es el dueño del token que vino en esta petición.
     */
    public function yo(Request $request): JsonResponse
    {
        return response()->json(
            $request->user()->only('id', 'name', 'email')
        );
    }

    /**
     * Reto: los dispositivos con sesión abierta.
     *
     * Solo salen los del usuario del token, y nunca la columna 'token'
     * (que guarda el hash).
     */
    public function tokens(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $request->user()->tokens()
                ->get(['id', 'name', 'last_used_at', 'created_at']),
        ]);
    }

    /**
     * Reto: cerrar la sesión de un dispositivo concreto.
     *
     * Se busca el token DENTRO de los del usuario: si se buscara por id
     * suelto, cualquiera podría revocar el token de otra persona.
     */
    public function revocarToken(Request $request, int $id): Response
    {
        $token = $request->user()->tokens()->where('id', $id)->first();

        abort_unless($token, 404);

        $token->delete();

        return response()->noContent();
    }

    /**
     * Borra solo el token con el que se hizo esta petición.
     * En una API no hay sesión que destruir: cerrar sesión es borrar
     * el registro del token.
     */
    public function logout(Request $request): Response
    {
        $request->user()->currentAccessToken()->delete();

        return response()->noContent();
    }
}
