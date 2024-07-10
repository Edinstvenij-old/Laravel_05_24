<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PhoneNumber implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Регулярний вираз для перевірки номера телефону з кодом країни або без нього
        $pattern = '/^\+?\d{1,4}?[-.\s]?\(?\d{1,4}?\)?[-.\s]?\d{1,9}$/';

        if (! preg_match($pattern, $value)) {
            $fail("The $attribute must be a valid phone number.");
        }
    }
}
