<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RecursoRequest extends FormRequest
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
        'id_subcategoria' => 'required|exists:subcategoria,id',
        'nombre' => 'required|string|max:255',
        'descripcion' => 'nullable|string|max:1000',
        'costo_unitario' => 'required|numeric|min:0',

        'id_usuario_creacion' => 'nullable',
        'id_usuario_modificacion' => 'nullable',
        'id_incidente_detalle' => 'nullable',
        'fecha_creacion' => 'nullable|date',
        'fecha_modificacion' => 'nullable|date',
    ];
}


}
