<?php

namespace App\Http\Resources\v1;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SchedulingResource extends JsonResource
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
            "pet_id" => $this->pet_id,
            "service_id" => $this->service_id,
            "date" => $this->date ? Carbon::make($this->date)->format("d/m/Y H:i:s") : $this->date,
            "finished" => $this->finished,
            "created_at" => Carbon::make($this->created_at)->format("d/m/Y H:i:s"),
            "updated_at" => Carbon::make($this->updated_at)->format("d/m/Y H:i:s")
        ];
    }
}
