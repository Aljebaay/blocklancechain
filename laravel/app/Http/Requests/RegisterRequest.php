<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * RegisterRequest - validates registration form data.
 */
class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'seller_user_name' => ['required', 'string', 'min:3', 'max:50', 'alpha_dash', 'unique:sellers,seller_user_name'],
            'seller_email' => ['required', 'email', 'max:255', 'unique:sellers,seller_email'],
            'seller_pass' => ['required', 'string', 'min:6'],
            'seller_name' => ['nullable', 'string', 'max:255'],
            'seller_country' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'seller_user_name.required' => 'Username is required.',
            'seller_user_name.unique' => 'This username is already taken.',
            'seller_email.required' => 'Email is required.',
            'seller_email.unique' => 'This email is already registered.',
            'seller_pass.required' => 'Password is required.',
            'seller_pass.min' => 'Password must be at least 6 characters.',
        ];
    }
}
