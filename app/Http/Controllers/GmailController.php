<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class GmailController extends Controller
{
    /**
     * Muestra la vista principal para obtener el token
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Obtener el client_id desde las variables de entorno
        $clientId = env('GOOGLE_CLIENT_ID');

        // Verificar si hay un token almacenado en la sesión
        $token = Session::get('gmail_token');

        $data = [
            'clientId' => $clientId,
            'tokenMessage' => $token ? $token : 'No hay token todavía',
            'buttonDisabled' => $token ? '' : 'disabled'
        ];

        // Pasamos los datos a la vista usando compact()
        return view('gmail.mi_vista', $data);
    }

    /**
     * Guarda el token recibido en la sesión
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveToken(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required|string',
        ]);

        // Guardar el token en la sesión
        Session::put('gmail_token', $validated['token']);

        return response()->json(['success' => true, 'message' => 'Token guardado correctamente']);
    }

    /**
     * Limpia el token almacenado
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function clearToken()
    {
        Session::forget('gmail_token');
        return redirect()->route('gmail.index')->with('message', 'Token eliminado');
    }
}
