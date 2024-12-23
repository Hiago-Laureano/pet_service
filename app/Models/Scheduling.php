<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @OA\Schema(
 *     schema="Scheduling",
 *     title="Scheduling",
 *     description="Scheduling Model",
 *     @OA\Property(property="id", type="integer", description="Id do agendamento"),
 *     @OA\Property(property="user_id", type="integer", description="Id do usuário"), 
 *     @OA\Property(property="pet_id", type="integer", description="Id do pet"), 
 *     @OA\Property(property="service_id", type="integer", description="Id do serviço"), 
 *     @OA\Property(property="date", type="string", pattern="2024/12/11 11:12:40", description="Data do agendamento"),
 *     @OA\Property(property="finished", type="boolean", description="Se o agendamento foi finalizado"),
 *     @OA\Property(property="created_at", type="string", pattern="20/12/2024 11:12:40", description="Data da criação do registro"),
 *     @OA\Property(property="updated_at", type="string", pattern="21/12/2024 11:15:30", description="Data da atualização do registro"),
 * )
 */
class Scheduling extends Model
{
    /** @use HasFactory<\Database\Factories\SchedulingFactory> */
    use HasFactory;

    protected $fillable = [
        "user_id",
        "pet_id",
        "service_id",
        "date",
        "finished"
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pet(): BelongsTo
    {
        return $this->belongsTo(Pet::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
