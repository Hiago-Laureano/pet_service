<?php

namespace App\Http\Requests\v1;

use Illuminate\Foundation\Http\FormRequest;

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
            "user_id" => ["nullable", "integer"],
            "pet_id" => ["required", "integer"],
            "service_id" => ["required", "integer"],
            "date" => ["required", "date_format:Y-m-d H:i:s"],
            "finished" => ["nullable", "boolean"]
        ];

        if($this->method() === "PATCH"){
            $rules["pet_id"] = ["nullable", "integer"];
            $rules["service_id"] = ["nullable", "integer"];
            $rules["date"] = ["nullable", "date_format:Y-m-d H:i:s"];
        }

        return $rules;
    }
}
