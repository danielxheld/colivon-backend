<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreChoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Check if user is member of the household
        return $this->user()->households->contains($this->input('household_id'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'household_id' => 'required|exists:households,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'recurrence_type' => 'required|in:daily,weekly,biweekly,monthly,custom,once',
            'recurrence_interval' => 'required_if:recurrence_type,custom|nullable|integer|min:1',
            'difficulty_points' => 'nullable|integer|min:1|max:5',
            'estimated_duration' => 'nullable|integer|min:1',
            'requires_photo' => 'nullable|boolean',
            'assignment_mode' => 'nullable|in:auto,manual,roulette',
        ];
    }
}
