<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\ValidationRule;
use Closure;

class PasswordComplexityRule implements ValidationRule
{
    protected int $minScore;

    public function __construct(int $minScore)
    {
        $this->minScore = $minScore;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $score = $this->calculateScore($value);
        if ($score < $this->minScore) {
            $fail("Kata sandi ini terlalu lemah. Silakan gunakan kombinasi karakter yang lebih bervariasi.");
        }
    }

    /**
     * Calculate a basic zxcvbn-like complexity score (1 to 4).
     */
    protected function calculateScore(string $password): int
    {
        $score = 1;
        $length = strlen($password);
        
        // Base score based on length thresholds
        if ($length >= 10) {
            $score++;
        }
        if ($length >= 14) {
            $score++;
        }

        // Diversity of character types
        $hasUpper   = preg_match('/[A-Z]/', $password);
        $hasLower   = preg_match('/[a-z]/', $password);
        $hasDigit   = preg_match('/[0-9]/', $password);
        $hasSpecial = preg_match('/[^A-Za-z0-9]/', $password);

        $varieties = $hasUpper + $hasLower + $hasDigit + $hasSpecial;
        
        if ($varieties >= 3) {
            $score++;
        }
        if ($varieties === 4 && $length >= 12) {
            $score++;
        }

        // Cap complexity score between 1 and 4
        return min(4, max(1, $score));
    }
}
