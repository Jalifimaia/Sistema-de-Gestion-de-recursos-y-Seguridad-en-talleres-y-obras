<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EstadoIncidenteRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para hacer esta solicitud.
     */
    public function authorize(): bool
    {
        return true; // Podés agregar lógica de roles si querés restringir
    }

    /**
     * Reglas de validación para crear o actualizar un estado de incidente.
     */
    public function rules(): array
    {
        return [
            'nombre_estado' => 'required|string|max:50|unique:estado_incidente,nombre_estado,' . $this->id,
        ];
    }
}
