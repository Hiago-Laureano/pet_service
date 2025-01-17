<?php

namespace App\Http\Requests\v1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUpdateSchedulingRequest extends FormRequest
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
            "pet_id" => ["required", "integer", Rule::exists("pets", "id")],
            "service_id" => ["required", "integer", Rule::exists("services", "id")],
            "date" => ["required", "date_format:Y-m-d H:i:s"],
            "finished" => ["nullable", "boolean"]
        ];

        if($this->method() === "PATCH"){
            $rules["pet_id"] = ["nullable", "integer", Rule::exists("pets", "id")];
            $rules["service_id"] = ["nullable", "integer", Rule::exists("services", "id")];
            $rules["date"] = ["nullable", "date_format:Y-m-d H:i:s"];
        }

        return $rules;
    }
}
