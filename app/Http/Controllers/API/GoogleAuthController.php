<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Google\Client as GoogleClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class GoogleAuthController extends Controller
{




    /**
     * Maneja la autenticación mediante Google ID Token
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function authenticateWithGoogle(Request $request)
    {
        // Validar la solicitud
        $validator = Validator::make($request->all(), [
            'id_token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Token de ID requerido',
                'errors' => $validator->errors()
            ], 422);
        }

        $idToken = $request->id_token;

        try {
            // Configurar el cliente de Google
            $client = new GoogleClient([
                'client_id' => config('services.google.client_id'),
            ]);


            // Verificar el ID token
            $payload = $client->verifyIdToken($idToken);


            if (!$payload) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token de ID inválido',
                ], 401);
            }

            // Extraer información del usuario
            $googleId = $payload['sub'];
            $email = $payload['email'] ?? null;
            $name = $payload['name'] ?? null;
            $picture = $payload['picture'] ?? null;

            // Buscar usuario existente o crear uno nuevo
            $user = User::where('google_id', $googleId)->first();

            if (!$user && $email) {
                // Verificar si existe un usuario con ese email
                $user = User::where('email', $email)->first();

                if ($user) {
                    // Actualizar el google_id del usuario existente
                    $user->google_id = $googleId;
                    $user->save();
                } else {
                    // Crear un nuevo usuario
                    $user = User::create([
                        'name' => $name ?? 'Usuario de Google',
                        'email' => $email,
                        'google_id' => $googleId,
                        'profile_photo' => $picture,
                        'password' => Hash::make(Str::random(16)), // Contraseña aleatoria
                        'email_verified_at' => now(), // El email ya está verificado por Google
                    ]);
                }
            }

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo encontrar o crear el usuario',
                ], 500);
            }

            // Generar token con Sanctum
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'profile_photo' => $user->profile_photo,
                ],
                'token' => $token,
                'token_type' => 'Bearer',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la autenticación: ' . $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Cierra sesión eliminando el token actual
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        // Revocar el token actual
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sesión cerrada correctamente',
        ]);
    }
}
