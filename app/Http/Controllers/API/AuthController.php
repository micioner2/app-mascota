<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Registro de nuevos usuarios con email y contraseña
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => [
                'required',
                'confirmed',
                PasswordRule::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
            ],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Verificar si ya existe un usuario con el mismo email (por Google)
        $existingUser = User::where('email', $request->email)->first();

        if ($existingUser) {
            // Si el usuario ya existe por Google pero sin contraseña
            if (empty($existingUser->password)) {
                $existingUser->password = Hash::make($request->password);
                $existingUser->save();

                // Generar token para el usuario existente
                $token = $existingUser->createToken('auth_token')->plainTextToken;

                return response()->json([
                    'message' => 'Contraseña establecida correctamente',
                    'user' => $existingUser,
                    'token' => $token
                ], 200);
            }

            return response()->json([
                'message' => 'El email ya está registrado',
            ], 409);
        }

        // Crear un nuevo usuario
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Usuario registrado exitosamente',
            'user' => $user,
            'token' => $token
        ], 201);
    }

    /**
     * Login con email y contraseña
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::where('email', $request->email)->first();

        // Verificar si el usuario existe
        if (!$user) {
            return response()->json([
                'message' => 'Credenciales incorrectas',
            ], 401);
        }

        // Verificar si el usuario tiene contraseña (podría no tenerla si se registró solo con Google)
        if (empty($user->password)) {
            return response()->json([
                'message' => 'Esta cuenta fue creada con Google. Por favor, inicie sesión con Google o establezca una contraseña.',
                'needs_password' => true
            ], 400);
        }

        // Verificar contraseña
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Credenciales incorrectas',
            ], 401);
        }

        // Nombre del dispositivo para el token (útil para identificar desde dónde se conecta)
        $deviceName = $request->device_name ?? $request->userAgent() ?? 'unknown device';

        // Crear token
        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'message' => 'Login exitoso',
            'user' => $user,
            'token' => $token
        ], 200);
    }

    /**
     * Login o registro con Google
     */
    public function googleAuth(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'google_id' => 'required|string',
            'email' => 'required|email',
            'name' => 'required|string',
            'profile_photo' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Buscar usuario por google_id o email
        $user = User::where('google_id', $request->google_id)
            ->orWhere('email', $request->email)
            ->first();

        if ($user) {
            // Actualizar información si es necesario
            if (empty($user->google_id)) {
                $user->google_id = $request->google_id;
            }

            // Actualizar otros campos si es necesario
            $user->name = $request->name;
            if ($request->profile_photo) {
                $user->profile_photo = $request->profile_photo;
            }
            $user->save();
        } else {
            // Crear nuevo usuario
            $user = User::create([
                'google_id' => $request->google_id,
                'name' => $request->name,
                'email' => $request->email,
                'profile_photo' => $request->profile_photo ?? null,
                'email_verified_at' => now(), // Al venir de Google, el email ya está verificado
            ]);
        }

        // Crear token
        $token = $user->createToken('google_auth')->plainTextToken;

        return response()->json([
            'message' => 'Login con Google exitoso',
            'user' => $user,
            'token' => $token,
            'has_password' => !empty($user->password)
        ], 200);
    }

    /**
     * Establecer o cambiar contraseña
     */
    public function setPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'sometimes|required',
            'password' => [
                'required',
                'confirmed',
                PasswordRule::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
            ],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Obtener usuario autenticado
        $user = auth()->user();

        // Si el usuario ya tiene contraseña, verificar la actual
        if (!empty($user->password)) {
            if (!$request->has('current_password')) {
                return response()->json([
                    'message' => 'Debe proporcionar la contraseña actual',
                ], 422);
            }

            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'message' => 'La contraseña actual es incorrecta',
                ], 401);
            }
        }

        // Establecer la nueva contraseña
        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            'message' => 'Contraseña actualizada correctamente',
        ], 200);
    }

    /**
     * Solicitar enlace de restablecimiento de contraseña
     */
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Verificar si el usuario existe y tiene contraseña
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'message' => 'Si su correo está registrado, recibirá un enlace para restablecer la contraseña.',
            ], 200); // Respuesta 200 para no revelar si el email existe
        }

        if (empty($user->password) && !empty($user->google_id)) {
            return response()->json([
                'message' => 'Esta cuenta fue creada con Google. No tiene contraseña para restablecer.',
                'google_account' => true
            ], 400);
        }

        // Enviar el enlace de restablecimiento
        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'message' => 'Enlace de restablecimiento enviado a su correo electrónico'
            ], 200);
        }

        return response()->json([
            'message' => 'No se pudo enviar el enlace de restablecimiento'
        ], 500);
    }

    /**
     * Restablecer contraseña
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'email' => 'required|email',
            'password' => [
                'required',
                'confirmed',
                PasswordRule::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
            ],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->password = Hash::make($password);
                $user->remember_token = Str::random(60);
                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'message' => 'Contraseña restablecida correctamente'
            ], 200);
        }

        return response()->json([
            'message' => 'No se pudo restablecer la contraseña'
        ], 500);
    }

    /**
     * Cerrar sesión (revocar token)
     */
    public function logout(Request $request)
    {
        // Revocar el token actual
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Sesión cerrada correctamente',
        ], 200);
    }

    /**
     * Cerrar sesión en todos los dispositivos
     */
    public function logoutAll(Request $request)
    {
        // Revocar todos los tokens del usuario
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Sesión cerrada en todos los dispositivos',
        ], 200);
    }

    /**
     * Obtener información del usuario autenticado
     */
    public function user(Request $request)
    {
        return response()->json([
            'user' => $request->user(),
        ], 200);
    }
}
