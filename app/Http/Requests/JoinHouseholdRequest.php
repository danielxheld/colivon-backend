<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JoinHouseholdRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Any authenticated user can join a household
    }

    public function rules(): array
    {
        return [
            'invite_code' => 'required|string|exists:households,invite_code',
        ];
    }

    public function messages(): array
    {
        return [
            'invite_code.required' => 'Please provide an invite code.',
            'invite_code.exists' => 'Invalid invite code. Please check and try again.',
        ];
    }
}
