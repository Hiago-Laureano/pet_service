<?php

namespace App\Http\Resources\v1;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            "first_name" => $this->first_name,
            "last_name" => $this->last_name,
            "phone" => $this->phone,
            "email" => $this->email,
            "email_verified_at" => $this->email_verified_at === null ? null : Carbon::make($this->email_verified_at)->format("d/m/Y H:i:s"),
            "is_staff" => $this->is_staff,
            "is_superuser" => $this->is_superuser,
            "pets" => PetResource::collection($this->pets),
            "schedulings" => SchedulingResource::collection($this->schedulings),
            "created_at" => Carbon::make($this->created_at)->format("d/m/Y H:i:s"),
            "updated_at" => Carbon::make($this->updated_at)->format("d/m/Y H:i:s")
        ];
    }
}
