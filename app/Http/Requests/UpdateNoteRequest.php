<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'body'       => 'sometimes|string',
            'contact_id' => 'nullable|integer|exists:contacts,id',
        ];
    }
}
