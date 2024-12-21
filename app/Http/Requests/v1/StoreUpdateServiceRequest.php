<?php

namespace App\Http\Requests\v1;

use Illuminate\Foundation\Http\FormRequest;

class StoreUpdateServiceRequest extends FormRequest
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
            "name" => ["required", "min:2", "max:255"],
            "price" => ["required", "numeric", "max:100000"]
        ];

        if($this->method() === "PATCH"){
            $rules["name"] = ["nullable", "min:2", "max:255"];
            $rules["price"] = ["nullable", "numeric", "max:100000"];
        }
        return $rules;
    }
}
