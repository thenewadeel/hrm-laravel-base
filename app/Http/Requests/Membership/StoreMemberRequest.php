<?php

namespace App\Http\Requests\Membership;

use Illuminate\Foundation\Http\FormRequest;

class StoreMemberRequest extends FormRequest
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
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female,other',
            'email' => 'nullable|email|unique:members,email',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // 2MB max
            'join_date' => 'required|date|before_or_equal:today',
            'expiry_date' => 'nullable|date|after:join_date',
            'notes' => 'nullable|string|max:1000',
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
            'first_name.required' => 'First name is required.',
            'last_name.required' => 'Last name is required.',
            'email.unique' => 'This email is already taken.',
            'email.email' => 'Please provide a valid email address.',
            'date_of_birth.before' => 'Date of birth must be before today.',
            'join_date.required' => 'Join date is required.',
            'join_date.before_or_equal' => 'Join date cannot be in the future.',
            'expiry_date.after' => 'Expiry date must be after join date.',
            'photo.image' => 'Please upload a valid image file.',
            'photo.mimes' => 'Photo must be a JPEG or PNG file.',
            'photo.max' => 'Photo may not be larger than 2MB.',
        ];
    }
}
