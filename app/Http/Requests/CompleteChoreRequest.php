<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompleteChoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $assignment = $this->route('assignment');
        // User must be the assignee or a household member
        return $this->user()->id === $assignment->user_id ||
               $this->user()->households->contains($assignment->chore->household_id);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'photo' => 'nullable|image|max:5120', // 5MB max
            'notes' => 'nullable|string|max:1000',
        ];
    }
}
