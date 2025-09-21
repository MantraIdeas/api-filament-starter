<?php

namespace App\Http\Requests\V1\Authentication;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            /**
             * Email of User
             *
             * @var string $email
             *
             * @example user@gmail.com
             */
            'email' => ['required', 'email', 'exists:users,email'],
            /**
             * OTP Code
             *
             * @var string $otp
             *
             * @example 1234
             */
            'otp' => ['required', 'int', 'digits:4'],
            /**
             * New Password
             *
             * @var string $password
             *
             * @example password
             */
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            /**
             * Confirm Password
             *
             * @var string $password_confirmation
             *
             * @example password
             */
            'password_confirmation' => ['required', 'string', 'min:8', 'same:password'],
        ];
    }
}
