<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\MascotaRequest;
use App\Models\Mascota;
use Illuminate\Support\Facades\Validator;

class MascotaController extends Controller
{
    public function index()
    {
        // Solo mostrar las mascotas del usuario autenticado
        $mascotas = Mascota::with(['tipoMascota', 'user'])
            ->where('user_id', auth()->id())
            ->get();

        return response()->json([
            'success' => true,
            'data' => $mascotas
        ], 200);
    }

    public function store(MascotaRequest $request)
    {

        $mascota = Mascota::create(array_merge(
            $request->validated(),
            ['user_id' => auth()->id()]
        ));

        return response()->json([
            'success' => true,
            'data' => $mascota->load(['tipoMascota', 'user'])
        ], 201);
    }

    public function show($id)
    {
        $mascota = Mascota::with('tipoMascota')->find($id);
        
        if (!$mascota) {
            return response()->json([
                'success' => false,
                'message' => 'Mascota no encontrada'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $mascota
        ], 200);
    }

     public function update(MascotaRequest $request, $id)
    {
        $mascota = Mascota::find($id);

        if (!$mascota) {
            return response()->json([
                'success' => false,
                'message' => 'Mascota no encontrada'
            ], 404);
        }

        $mascota->update($request->validated());

        return response()->json([
            'success' => true,
            'data' => $mascota->load(['tipoMascota', 'user'])
        ], 200);
    }

    public function destroy($id)
    {
        $mascota = Mascota::find($id);

        if (!$mascota) {
            return response()->json([
                'success' => false,
                'message' => 'Mascota no encontrada'
            ], 404);
        }

        $mascota->delete();

        return response()->json([
            'success' => true,
            'message' => 'Mascota eliminada correctamente'
        ], 200);
    }
}
