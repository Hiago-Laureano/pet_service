<?php

namespace App\Http\Resources\v1;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MedicalRecordResource extends JsonResource
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
            "access_code" => $this->access_code,
            "user_id" => $this->user_id,
            "pet_id" => $this->pet_id,
            "observation" => $this->observation,
            "created_at" => Carbon::make($this->created_at),
            "updated_at" => Carbon::make($this->created_at)
        ];
    }
}
