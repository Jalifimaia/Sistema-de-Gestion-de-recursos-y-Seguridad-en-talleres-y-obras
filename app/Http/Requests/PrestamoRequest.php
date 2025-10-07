<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PrestamoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fecha_prestamo'   => 'required|date',
            'fecha_devolucion' => 'nullable|date',
            'estado'           => 'required|integer',
            'id_serie'         => 'required|integer|exists:serie_recurso,id',
        ];
    }
}
