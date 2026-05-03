<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:150',
            'phone'   => 'required|string|min:9|max:20',
            'service' => 'required|string',
            'message' => 'required|string|min:10|max:2000',
        ];
    }
}