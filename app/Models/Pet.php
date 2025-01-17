<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @OA\Schema(
 *     schema="Pet",
 *     title="Pet",
 *     description="Pet Model",
 *     @OA\Property(property="id", type="integer", description="Id do pet"),
 *     @OA\Property(property="user_id", type="integer", description="Id do usuário dono do pet"), 
 *     @OA\Property(property="name", type="string", description="Nome"),
 *     @OA\Property(property="species", type="string", description="Espécie"),
 *     @OA\Property(property="breed", type="string", description="Raça"),
 *     @OA\Property(property="weight", type="number", description="Peso"),
 *     @OA\Property(property="age", type="integer", description="Idade", nullable=true),
 *     @OA\Property(property="gender", type="string", pattern="M", description="Sexo, 'M' para macho e 'F' para fêmea"),
 *     @OA\Property(property="agressive", type="boolean", description="Se é agressivo"),
 *     @OA\Property(property="medical_records", type="array", description="Lista com todos os dados dos prontuários médicos do pet", @OA\items(ref="#/components/schemas/MedicalRecord")),
 *     @OA\Property(property="created_at", type="string", pattern="20/12/2024 11:12:40", description="Data da criação do registro"),
 *     @OA\Property(property="updated_at", type="string", pattern="21/12/2024 11:15:30", description="Data da atualização do registro"),
 * )
 */
class Pet extends Model
{
    /** @use HasFactory<\Database\Factories\PetFactory> */
    use HasFactory;

    protected $fillable = [
        "user_id",
        "name",
        "species",
        "breed",
        "weight",
        "age",
        "gender",
        "agressive",
        "active"
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function schedulings(): HasMany
    {
        return $this->hasMany(Pet::class);
    }

    protected function medicalRecords(): HasMany
    {
        return $this->hasMany(MedicalRecord::class);
    }
}
