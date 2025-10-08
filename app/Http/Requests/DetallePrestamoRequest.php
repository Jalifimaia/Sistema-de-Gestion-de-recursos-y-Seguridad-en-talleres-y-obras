<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DetallePrestamoRequest extends FormRequest
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
            'id_prestamo' => 'required|integer|exists:prestamo,id',
            'id_serie' => 'required|integer|exists:serie_recurso,id',
            'id_recurso' => 'required|integer|exists:recurso,id',
                'id_estado_prestamo' => 'required|integer|exists:estado_prestamo,id', // Ensure this line is included
        ];
    }
}
