<?php

namespace App\Http\Requests\Chat;

use Illuminate\Foundation\Http\FormRequest;

class CreateConversationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'visitor_name'  => ['required', 'string', 'max:100'],
            'visitor_phone' => ['nullable', 'string', 'max:30'],
            'visitor_email' => ['nullable', 'email', 'max:150'],
            'page_url'      => ['nullable', 'url', 'max:500'],
            'locale'        => ['nullable', 'in:es,ca,en,fr'],
            'consent'       => ['required', 'accepted'],
            // honeypot: debe llegar vacío
            'website'       => ['nullable', 'max:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'visitor_name.required' => 'Por favor, indica tu nombre.',
            'visitor_name.max'      => 'El nombre no puede superar los 100 caracteres.',
            'visitor_email.email'   => 'El correo electrónico no es válido.',
            'consent.accepted'      => 'Debes aceptar el aviso de privacidad para continuar.',
            'website.max'           => 'No se ha podido procesar la solicitud.',
        ];
    }
}
