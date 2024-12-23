<?php

namespace App\Http\Requests\v1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            "name" => ["required", "min:2", "max:255", Rule::unique("services")],
            "price" => ["required", "numeric", "max:100000"]
        ];

        if($this->method() === "PUT"){
            $rules["name"] = ["required", "min:2", "max:255", Rule::unique("services")->ignore($this->id)];
        }

        if($this->method() === "PATCH"){
            $rules["name"] = ["nullable", "min:2", "max:255", Rule::unique("services")->ignore($this->id)];
            $rules["price"] = ["nullable", "numeric", "max:100000"];
        }
        return $rules;
    }
}
