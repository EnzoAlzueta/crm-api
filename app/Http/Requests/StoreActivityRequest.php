<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreActivityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type'         => 'nullable|string|max:50',
            'title'        => 'required|string|max:255',
            'body'         => 'nullable|string',
            'due_at'       => 'nullable|date',
            'completed_at' => 'nullable|date',
            'contact_id'   => 'nullable|integer|exists:contacts,id',
        ];
    }
}
