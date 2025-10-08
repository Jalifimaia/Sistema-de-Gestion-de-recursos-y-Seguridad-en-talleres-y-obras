<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
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
    $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'id_rol' => 'required|exists:rol,id',
        'id_estado' => 'required|exists:estado_usuario,id',
        'fecha_nacimiento' => 'nullable|date',
        'dni' => 'nullable|string|max:15',
        'telefono' => 'nullable|string|max:30',
        'nro_legajo' => 'nullable|integer',
        'auth_key' => 'nullable|string|max:255',
        'access_token' => 'nullable|string|max:255',
    ];

    if ($this->isMethod('post')) {
        $rules['password'] = 'required|string|min:6|confirmed';
    } else {
        $rules['password'] = 'nullable|string|min:6|confirmed';
    }

    return $rules;
}

}
