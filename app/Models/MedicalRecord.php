<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @OA\Schema(
 *     schema="MedicalRecord",
 *     title="MedicalRecord",
 *     description="MedicalRecord Model",
 *     @OA\Property(property="id", type="integer", description="Id do MedicalRecord"),
 *     @OA\Property(property="access_code", type="string", description="Código de acesso do prontuário"),
 *     @OA\Property(property="user_id", type="integer", description="Id do usuário dono do pet"), 
 *     @OA\Property(property="pet_id", type="integer", description="Id do pet"), 
 *     @OA\Property(property="observation", type="string", description="Observações"),
 *     @OA\Property(property="created_at", type="string", pattern="20/12/2024 11:12:40", description="Data da criação do registro"),
 *     @OA\Property(property="updated_at", type="string", pattern="21/12/2024 11:15:30", description="Data da atualização do registro"),
 * )
 */
class MedicalRecord extends Model
{
    /** @use HasFactory<\Database\Factories\MedicalRecordFactory> */
    use HasFactory;

    protected $fillable = [
        "access_code",
        "user_id",
        "pet_id",
        "observation"
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pet(): BelongsTo
    {
        return $this->belongsTo(Pet::class);
    }
}
