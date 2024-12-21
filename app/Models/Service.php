<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Service",
 *     title="Service",
 *     description="Service Model",
 *     @OA\Property(property="id", type="integer", description="Id do serviço"),
 *     @OA\Property(property="name", type="string", description="Nome"),
 *     @OA\Property(property="price", type="number", description="Preço"),
 *     @OA\Property(property="created_at", type="string", pattern="20/12/2024 11:12:40", description="Data da criação do registro"),
 *     @OA\Property(property="updated_at", type="string", pattern="21/12/2024 11:15:30", description="Data da atualização do registro"),
 * )
 */
class Service extends Model
{
    /** @use HasFactory<\Database\Factories\ServiceFactory> */
    use HasFactory;

    protected $fillable = [
        "name",
        "price",
        "active"
    ];
}
