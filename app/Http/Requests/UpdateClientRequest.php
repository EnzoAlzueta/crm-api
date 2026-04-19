<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'    => 'sometimes|string|max:255',
            'email'   => 'nullable|email',
            'phone'   => 'nullable|string',
            'company' => 'nullable|string',
            'status'  => 'nullable|string',
        ];
    }
}
