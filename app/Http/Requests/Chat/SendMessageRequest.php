<?php

namespace App\Http\Requests\Chat;

use Illuminate\Foundation\Http\FormRequest;

class SendMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'body'       => ['required_without:attachment', 'nullable', 'string', 'max:2000'],
            'attachment' => [
                'required_without:body',
                'nullable',
                'file',
                'max:5120', // 5 MB en KB
                'mimes:jpg,jpeg,png,webp',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'body.required_without'       => 'El mensaje no puede estar vacío.',
            'body.max'                    => 'El mensaje no puede superar los 2000 caracteres.',
            'attachment.required_without' => 'Adjunta una imagen o escribe un mensaje.',
            'attachment.max'              => 'La imagen no puede superar los 5 MB.',
            'attachment.mimes'            => 'Solo se permiten imágenes JPG, PNG o WEBP.',
        ];
    }
}
