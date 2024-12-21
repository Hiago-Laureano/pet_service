<?php

namespace App\Http\Resources\v1;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PetResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "user_id" => $this->user_id,
            "name" => $this->name,
            "species" => $this->species,
            "breed" => $this->breed,
            "weight" => $this->weight,
            "age" => $this->age,
            "gender" => $this->gender,
            "agressive" => $this->agressive,
            "created_at" => Carbon::make($this->created_at)->format("d/m/Y H:i:s"),
            "updated_at" => Carbon::make($this->updated_at)->format("d/m/Y H:i:s")
        ];
    }
}
