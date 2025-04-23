<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Google\Client as GoogleClient;
use App\Http\Controllers\API\GoogleAuthController;

class GoogleAuthTestController extends Controller
{
    /**
     * Mostrar la página de inicio para las pruebas
     *
     * @return \Illuminate\View\View
     */
    public function showLoginPage()
    {
        return view('auth.google-test');
    }

    /**
     * Procesar los datos de prueba
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function processToken(Request $request)
    {
        // \Log::info('ID Token recibido:', ['id_token' => $request->id_token]);

        $request->validate([
            'id_token' => 'required|string',
        ]);

        // Usar el controlador API existente para procesar el token
        $apiController = new GoogleAuthController();
        $response = $apiController->authenticateWithGoogle($request);

        // Convertir la respuesta de la API a un array
        $data = json_decode($response->getContent(), true);

        // Si autenticación exitosa, guardar el token en la sesión para pruebas futuras
        if ($response->getStatusCode() === 200 && isset($data['token'])) {
            session(['auth_token' => $data['token']]);
        }

        return view('auth.google-result', ['response' => $data, 'status' => $response->getStatusCode()]);
    }


    /**
     * Probar la obtención de datos de usuario
     *
     * @return \Illuminate\View\View
     */
    public function testUserData()
    {
        $token = session('auth_token');

        if (!$token) {
            return redirect()->route('google.test')
                ->with('error', 'No hay un token de autenticación activo. Por favor inicia sesión primero.');
        }

        $client = new \GuzzleHttp\Client();

        try {
            // Llamada a la API interna
            $response = $client->request('GET', url('api/user'), [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $token,
                ],
            ]);

            $userData = json_decode($response->getBody()->getContents(), true);

            return view('auth.user-data', ['userData' => $userData, 'status' => $response->getStatusCode()]);
        } catch (\Exception $e) {
            return view('auth.user-data', [
                'error' => $e->getMessage(),
                'status' => $e->getCode()
            ]);
        }
    }

    /**
     * Probar el cierre de sesión
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function testLogout()
    {
        $token = session('auth_token');

        if (!$token) {
            return redirect()->route('google.test')
                ->with('error', 'No hay un token de autenticación activo. Por favor inicia sesión primero.');
        }

        $client = new \GuzzleHttp\Client();

        try {
            // Llamada a la API interna de logout
            $response = $client->request('POST', url('api/auth/logout'), [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $token,
                ],
            ]);

            // Limpiar el token de la sesión
            session()->forget('auth_token');

            return redirect()->route('google.test')
                ->with('success', 'Sesión cerrada correctamente. Código de estado: ' . $response->getStatusCode());
        } catch (\Exception $e) {
            return redirect()->route('google.test')
                ->with('error', 'Error al cerrar sesión: ' . $e->getMessage());
        }
    }
}
