<?php

namespace App\Http\Requests\Chat;

use Illuminate\Foundation\Http\FormRequest;

class UpdateContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone' => ['nullable', 'required_without:email', 'string', 'regex:/^\+?[0-9\s\-().]{7,20}$/'],
            'email' => ['nullable', 'required_without:phone', 'email', 'max:150'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.required_without' => 'Indica un teléfono o un correo electrónico.',
            'email.required_without' => 'Indica un teléfono o un correo electrónico.',
            'phone.regex'            => 'El teléfono no tiene un formato válido (ej. 678 758 210 o +34 678 758 210).',
            'email.email'            => 'El correo electrónico no es válido.',
        ];
    }
}
