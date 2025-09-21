<?php

namespace App\Http\Requests\V1\Authentication;

use App\Enums\VerifyOtpForEnum;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VerifyOtpRequest extends FormRequest
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
             * Verify For
             *
             * @var string $verify_for
             *
             * @example registration
             */
            'verify_for' => ['required', 'string', Rule::enum(VerifyOtpForEnum::class)],
        ];
    }
}
