<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * LoginRequest - validates login form data.
 * Replaces legacy Validator usage in login.php.
 */
class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'seller_user_name' => ['required', 'string', 'max:255'],
            'seller_pass' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'seller_user_name.required' => 'Username is required.',
            'seller_pass.required' => 'Password is required.',
        ];
    }
}
