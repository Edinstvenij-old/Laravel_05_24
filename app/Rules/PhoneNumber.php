<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PhoneNumber implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Регулярное выражение для проверки телефона. Можете изменить его в зависимости от требований.
        $pattern = '/^\+?[1-9]\d{1,14}$/'; // Простой формат для международных номеров

        if (! preg_match($pattern, $value)) {
            $fail("The $attribute must be a valid phone number.");
        }
    }
}

