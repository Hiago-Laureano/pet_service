<?php

namespace App\Http\Requests\v1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUpdatePetRequest extends FormRequest
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
            "user_id" => ["nullable", "integer", Rule::exists("users", "id")],
            "name" => ["required", "min:3", "max:100"],
            "species" => ["required", "min:3", "max:100"],
            "breed" => ["required", "min:3", "max:50"],
            "weight" => ["required", "numeric", "max:1000"],
            "age" => ["nullable", "integer", "min_digits:1", "max_digits:2"],
            "gender" => ["required", "min:1", "max:1", Rule::in(["M", "F"])],
            "agressive" => ["required", "boolean"]
        ];

        if($this->method() === "PATCH"){
            $rules["name"] = ["nullable", "min:3", "max:100"];
            $rules["species"] = ["nullable", "min:3", "max:100"];
            $rules["breed"] = ["nullable", "min:3", "max:50"];
            $rules["weight"] = ["nullable", "numeric", "max:1000"];
            $rules["gender"] = ["nullable", "min:1", "max:1", Rule::in(["M", "F"])];
            $rules["agressive"] = ["nullable", "boolean"];
        }
        return $rules;
    }
}
