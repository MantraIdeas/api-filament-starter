<?php

namespace App\Http\Requests\V1\Authentication;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            /**
             * Name Of The User
             *
             * @var string $name
             *
             * @example User
             */
            'name' => ['required', 'string', 'max:255'],
            /**
             * Email Address Of The User
             *
             * @var string $email
             *
             * @example user@gmail.com
             */
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            /**
             * Password For The User Account
             *
             * @var string $password
             *
             * @example password
             */
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            /**
             * Password Confirmation
             *
             * @var string $password_confirmation
             *
             * @example password
             */
            'password_confirmation' => ['required', 'string', 'min:8', 'same:password'],
        ];
    }
}
