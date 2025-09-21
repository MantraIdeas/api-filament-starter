<?php

namespace App\Http\Requests\V1\Authentication;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
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
             * The old password field is required.
             *
             * @var string $old_password
             *
             * @example password
             */
            'old_password' => ['required', 'string', 'min:8'],

            /**
             * The new password field is required.
             *
             * @var string $new_password
             *
             * @example password
             */
            'new_password' => ['required', 'string', 'min:8', 'different:old_password'],

            /**
             * The confirm password field is required.
             *
             * @var string $confirm_password
             *
             * @example password
             */
            'confirm_password' => ['required', 'string', 'min:8', 'same:new_password'],
        ];
    }
}
