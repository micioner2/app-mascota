<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;

class ResetPasswordController extends Controller
{
    /**
     * Muestra el formulario de restablecimiento de contraseña
     */
    public function showResetForm(Request $request, $token)
    {
        if (!$token) {
            return redirect()->route('password.request')
                ->with('error', 'Token de restablecimiento de contraseña inválido.');
        }

        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->email
        ]);
    }

    /**
     * Procesa el restablecimiento de contraseña
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

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
            return redirect()->back()->with('success', 'Contraseña restablecida correctamente. Ahora puedes iniciar sesión con tu nueva contraseña.');
        }

        return redirect()->back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => [trans($status)]]);
    }
} 