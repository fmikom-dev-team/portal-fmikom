<?php

namespace App\Concerns;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Validation\Rules\Password;

trait PasswordValidationRules
{
    /**
     * Get the validation rules used to validate passwords.
     *
     * @return array<int, Rule|array<mixed>|string>
     */
    protected function passwordRules(): array
    {
        $rules = ['required', 'string', Password::default(), 'confirmed'];

        $complexity = (int) \App\Models\Auth\AuthSetting::get('email_password.complexity', 3);
        if ($complexity > 1) {
            $rules[] = new \App\Rules\PasswordComplexityRule($complexity);
        }

        $historyCount = (int) \App\Models\Auth\AuthSetting::get('password.history_count', 5);
        if ($historyCount > 0) {
            $rules[] = new \App\Rules\NotUsedPassword();
        }

        return $rules;
    }

    /**
     * Get the validation rules used to validate the current password.
     *
     * @return array<int, Rule|array<mixed>|string>
     */
    protected function currentPasswordRules(): array
    {
        return ['required', 'string', 'current_password'];
    }
}
