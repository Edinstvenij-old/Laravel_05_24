<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\PhoneNumber; // Убедитесь, что у вас есть это правило или удалите его, если не используется

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Разрешает доступ к этому запросу для всех пользователей
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'phone' => ['required', 'string', 'size:10', new PhoneNumber], // Проверьте, если у вас есть правило PhoneNumber
            'birthdate' => 'required|date_format:Y-m-d', // Убедитесь, что формат даты соответствует
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|confirmed|min:8', // Проверка на подтверждение и минимальную длину
        ];
    }
}
