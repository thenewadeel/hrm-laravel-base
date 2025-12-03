<?php

namespace App\Http\Requests\Membership;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMemberRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Will be handled by policies
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'nullable|in:Mr,Mrs,Ms,Dr',
            'first_name' => 'sometimes|string|max:100',
            'last_name' => 'sometimes|string|max:100',
            'date_of_birth' => 'sometimes|date|before:today',
            'gender' => 'sometimes|in:male,female,other',
            'email' => 'sometimes|email|unique:members,email,' . $this->route('member'),
            'phone' => 'sometimes|string|max:50',
            'address' => 'sometimes|string|max:500',
            'city' => 'sometimes|string|max:100',
            'state' => 'sometimes|string|max:100',
            'postal_code' => 'sometimes|string|max:20',
            'country' => 'sometimes|string|max:100',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // 2MB max
            'expiry_date' => 'sometimes|date',
            'notes' => 'sometimes|string|max:1000',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'first_name.string' => 'First name must be a string.',
            'last_name.string' => 'Last name must be a string.',
            'email.unique' => 'This email is already taken.',
            'email.email' => 'Please provide a valid email address.',
            'date_of_birth.before' => 'Date of birth must be before today.',
            'expiry_date.after' => 'Expiry date must be after join date.',
            'photo.image' => 'Please upload a valid image file.',
            'photo.mimes' => 'Photo must be a JPEG or PNG file.',
            'photo.max' => 'Photo may not be larger than 2MB.',
        ];
    }
}
