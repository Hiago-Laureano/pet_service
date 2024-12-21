<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @OA\Schema(
 *     schema="User",
 *     title="User",
 *     description="User Model",
 *     @OA\Property(property="id", type="integer", description="Id do usuário"),
 *     @OA\Property(property="first_time", type="string", description="Nome"),
 *     @OA\Property(property="last_name", type="string", description="Sobrenome"),
 *     @OA\Property(property="phone", type="integer", pattern="5500999990000", description="Número de telefone"),
 *     @OA\Property(property="email", type="string", description="E-mail"),
 *     @OA\Property(property="email_verified_at", type="string", pattern="20/12/2024 11:12:40", description="Data de verificação do e-mail", nullable=true),
 *     @OA\Property(property="is_staff", type="boolean", description="Se é membro da equipe"),
 *     @OA\Property(property="is_superuser", type="boolean", description="Se é superusuário"),
 *     @OA\Property(property="pets", type="array", description="Lista com todos os dados dos pets do usuário", @OA\items(ref="#/components/schemas/Pet")),
 *     @OA\Property(property="created_at", type="string", pattern="20/12/2024 11:12:40", description="Data da criação do registro"),
 *     @OA\Property(property="updated_at", type="string", pattern="21/12/2024 11:15:30", description="Data da atualização do registro"),
 * )
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        "first_name",
        "last_name",
        "phone",
        "email",
        "password",
        "is_staff",
        "is_superuser",
        "active"
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected function pets(): HasMany
    {
        return $this->hasMany(Pet::class);
    }
}
