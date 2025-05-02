<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\TipoMascota;
use Illuminate\Http\Request;

class TipoMascotaController extends Controller
{
    /**
     * Listar todos los tipos de mascota activos
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $tiposMascota = TipoMascota::whereNull('deleted_at')->get();
        
        return response()->json([
            'success' => true,
            'data' => $tiposMascota
        ], 200);
    }
}
