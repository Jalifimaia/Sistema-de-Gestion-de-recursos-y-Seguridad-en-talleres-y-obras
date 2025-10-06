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
			'id_recurso' => 'required',
			'id_supervisor' => 'required',
			'id_incidente_detalle' => 'required',
			'id_usuario_creacion' => 'required',
			'id_usuario_modificacion' => 'required',
			'descripcion' => 'string',
			'fecha_incidente' => 'required',
			'fecha_creacion' => 'required',
			'fecha_modificacion' => 'required',
			'fecha_cierre_incidente' => 'required',
			'resolucion' => 'string',
        ];
    }
}
