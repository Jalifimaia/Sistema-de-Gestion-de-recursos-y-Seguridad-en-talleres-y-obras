<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SerieRecursoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Cambiá si usás políticas
    }

    public function rules(): array
    {
        return [
            'id_recurso' => 'required|exists:recurso,id',
            'version' => 'required|integer|min:1|max:10',
            'anio' => 'required|integer|min:2000|max:' . date('Y'),
            'lote' => 'required|integer|min:1',
            'fecha_adquisicion' => 'required|date',
            'fecha_vencimiento' => 'nullable|date|after_or_equal:fecha_adquisicion',
            'id_estado' => 'required|exists:estado,id',
            'combinaciones' => 'required|json',
        ];
    }

    public function messages(): array
    {
        return [
            'id_recurso.required' => 'El recurso es obligatorio.',
            'version.required' => 'La versión es obligatoria.',
            'anio.required' => 'El año es obligatorio.',
            'lote.required' => 'El lote es obligatorio.',
            'fecha_adquisicion.required' => 'La fecha de adquisición es obligatoria.',
            'id_estado.required' => 'El estado es obligatorio.',
            'combinaciones.required' => 'Debés agregar al menos una combinación.',
            'combinaciones.json' => 'Las combinaciones deben estar en formato válido.',
        ];
    }
}
