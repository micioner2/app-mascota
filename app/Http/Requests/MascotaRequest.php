<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class MascotaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:255',
            'tipo_mascota_id' => 'required|exists:tipo_mascotas,id',
            'raza' => 'required|string|max:255',
            'fecha_nacimiento' => 'required|date',
            'descripcion' => 'required|string',
            'foto' => 'nullable|url'
        ];
    }

    public function messages()
    {
        return [
            'nombre.required' => 'El nombre de la mascota es obligatorio',
            'nombre.string' => 'El nombre debe ser texto',
            'nombre.max' => 'El nombre no debe exceder los 255 caracteres',
            'tipo_mascota_id.required' => 'El tipo de mascota es obligatorio',
            'tipo_mascota_id.exists' => 'El tipo de mascota seleccionado no es válido',
            'raza.required' => 'La raza es obligatoria',
            'raza.string' => 'La raza debe ser texto',
            'raza.max' => 'La raza no debe exceder los 255 caracteres',
            'fecha_nacimiento.required' => 'La fecha de nacimiento es obligatoria',
            'fecha_nacimiento.date' => 'Debe ingresar una fecha válida',
            'descripcion.required' => 'La descripción es obligatoria',
            'descripcion.string' => 'La descripción debe ser texto',
            'foto.url' => 'Debe ingresar una URL válida para la foto'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'errors' => $validator->errors()
        ], 422));
    }
}
