<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class ValidEmailDomain implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param string $attribute
     * @param mixed $value
     * @param \Closure(string, ?string=): PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // check that email contains at least one dot in the domain
        if (!preg_match('/^[^\s@]+@[^\s@]+\.[^\s@]+$/', $value)) {
            $fail('The :attribute must be a valid email with a properly formatted domain.');
        }
    }
}
