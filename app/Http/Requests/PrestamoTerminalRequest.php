<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PrestamoTerminalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Podés agregar lógica de permisos si querés
    }

    public function rules(): array
    {
        return [
            'series'   => ['required', 'array', 'min:1'],
            'series.*' => ['integer', 'exists:serie_recurso,id'],
            // No pedimos fechas ni trabajador: se completan en el controlador
        ];
    }
}
