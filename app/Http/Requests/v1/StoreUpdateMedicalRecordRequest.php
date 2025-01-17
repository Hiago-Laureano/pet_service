<?php

namespace App\Http\Requests\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUpdateMedicalRecordRequest extends FormRequest
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
            "user_id" => ["required", "integer", Rule::exists("users", "id")],
            "pet_id" => ["required", "integer", Rule::exists("pets", "id")],
            "observation" => ["required", "max:3000"],
        ];

        if($this->method() === "PATCH"){
            $rules["user_id"] = ["nullable", "integer", Rule::exists("users", "id")];
            $rules["pet_id"] = ["nullable", "integer", Rule::exists("pets", "id")];
            $rules["observation"] = ["nullable", "max:3000"];
        }
        return $rules;
    }
}
