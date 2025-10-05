<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PrestamoRequest extends FormRequest
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
			'id_usuario' => 'required',
			'id_usuario_creacion' => 'required',
			'id_usuario_modificacion' => 'required',
			'fecha_prestamo' => 'required',
			'estado' => 'required',
			'fecha_creacion' => 'required',
			'fecha_modificacion' => 'required',
        ];
    }
}
