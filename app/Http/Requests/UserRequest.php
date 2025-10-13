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
    $id = $this->route('usuario');

    $rules = [
        'name'   => 'required|string|max:255',
        'email'  => 'required|email|max:255|unique:usuario,email,' . $id,
        'id_rol' => 'required|exists:rol,id',
        'dni'    => 'required|string|max:15|unique:usuario,dni,' . $id,
        'auth_key' => 'nullable|string|max:255',
        'access_token' => 'nullable|string|max:255',
    ];

    if ($this->isMethod('post')) {
        // En creación, siempre obligatoria
        $rules['password'] = 'required|string|min:6|confirmed';
    } else {
        // En edición, solo validar si se envía password o confirmación
        $rules['password'] = 'nullable|string|min:6';
        $rules['password_confirmation'] = 'nullable|string|min:6';
    }

    return $rules;
}

}
