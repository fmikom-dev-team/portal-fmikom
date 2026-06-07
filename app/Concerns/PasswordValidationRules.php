<?php

namespace App\Concerns;

use App\Models\Auth\AuthSetting;
use App\Rules\NotUsedPassword;
use App\Rules\PasswordComplexityRule;
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

        $complexity = (int) AuthSetting::get('email_password.complexity', 3);
        if ($complexity > 1) {
            $rules[] = new PasswordComplexityRule($complexity);
        }

        $historyCount = (int) AuthSetting::get('password.history_count', 5);
        if ($historyCount > 0) {
            $rules[] = new NotUsedPassword;
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
