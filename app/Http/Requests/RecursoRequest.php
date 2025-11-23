<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RecursoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_subcategoria' => 'required|exists:subcategoria,id',
            'nombre' => 'required|string|max:255',
            'descripcion' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    if (str_word_count($value) > 4) {
                        $fail('La descripción debe tener como máximo 4 palabras.');
                    }
                },
            ],
            'costo_unitario' => [
                'required',
                'numeric',
                'min:0',
                'regex:/^\d+(\.\d{1,2})?$/',
            ],
            'id_usuario_creacion' => 'nullable',
            'id_usuario_modificacion' => 'nullable',
            'id_incidente_detalle' => 'nullable',
            'fecha_creacion' => 'nullable|date',
            'fecha_modificacion' => 'nullable|date',
        ];
    }

    public function messages(): array
    {
        return [
            'id_subcategoria.required' => 'La subcategoría es obligatoria.',
            'id_subcategoria.exists' => 'La subcategoría seleccionada no es válida.',
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.max' => 'El nombre no puede superar los 255 caracteres.',
            'descripcion.required' => 'La descripción es obligatoria.',
            'costo_unitario.required' => 'El costo unitario es obligatorio.',
            'costo_unitario.numeric' => 'El costo unitario debe ser un número válido.',
            'costo_unitario.min' => 'El costo unitario no puede ser negativo.',
            'costo_unitario.regex' => 'El formato del costo unitario no es válido.',
        ];
    }
}