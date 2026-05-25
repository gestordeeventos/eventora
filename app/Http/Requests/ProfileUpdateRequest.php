<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return ! $this->user()?->isProtectedAdmin();
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:80'],
            'apellido' => ['required', 'string', 'max:80'],
            'email' => [
                'required', 'string', 'lowercase', 'email', 'max:120',
                Rule::unique(User::class, 'email')->ignore($this->user()->id_usuario, 'id_usuario'),
            ],
            'telefono' => ['nullable', 'string', 'max:20'],
        ];
    }
}
