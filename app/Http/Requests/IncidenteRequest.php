<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IncidenteRequest extends FormRequest
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
        'id_recurso' => 'required|integer|exists:recurso,id',
        'id_supervisor' => 'required|integer|exists:usuario,id',
        'id_incidente_detalle' => 'nullable|integer|exists:incidente_detalle,id',
        'descripcion' => 'nullable|string|max:250',
        'fecha_incidente' => 'required|date',
        'fecha_cierre_incidente' => 'nullable|date',
        'resolucion' => 'nullable|string|max:250',
    ];
}

}
