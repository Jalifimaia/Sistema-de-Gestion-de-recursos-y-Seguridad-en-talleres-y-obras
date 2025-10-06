<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SerieRecursoRequest extends FormRequest
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
			'id_incidente_detalle' => 'required',
			'nro_serie' => 'required|unique:serie_recurso,nro_serie',
			'fecha_adquisicion' => 'required',
			'fecha_vencimiento' => 'required',
            'id_estado' => 'required,'
        ];
    }
}
