<?php

namespace App\Http\Requests\v1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUpdateUserRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            "first_name" => ["required", "min:3", "max:50"],
            "last_name" => ["required", "min:3", "max:50"],
            "phone" => ["required", "min_digits:12", "max_digits:13", "integer"],
            "email" => ["required", "email", "min:8", "max:100", Rule::unique("users")],
            "password" => ["required", "min:8", "max:100"],
            "is_staff" => ["nullable", "boolean"],
            "is_superuser" => ["nullable", "boolean"]
        ];

        if($this->method() === "PUT"){
            $rules["email"] = ["required", "email", "min:8", "max:100", Rule::unique("users")->ignore($this->email)];

        }
        if($this->method() === "PATCH"){
            $rules["email"] = ["nullable", "email", "min:8", "max:100", Rule::unique("users")->ignore($this->email)];
            $rules["first_name"] = ["nullable", "min:3", "max:50"];
            $rules["last_name"] = ["nullable", "min:3", "max:50"];
            $rules["phone"] = ["nullable", "min_digits:12", "max_digits:13", "integer"];
            $rules["password"] = ["nullable", "min:8", "max:100"];
        }
        return $rules;
    }
}
