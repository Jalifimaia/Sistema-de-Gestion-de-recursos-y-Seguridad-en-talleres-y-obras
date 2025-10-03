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
    ];

    if ($this->isMethod('post')) {
        $rules['password'] = 'required|string|min:6|confirmed';
    } else {
        $rules['password'] = 'nullable|string|min:6|confirmed';
    }

    return $rules;
}

}
