<?php

namespace App\Services;

use App\Models\Pet;
use App\Models\Scheduling;
use App\Models\User;

class Permissions
{
    public static function IsSuperuser(User $user): bool
    {
        return $user->is_superuser ? true : false;
    }

    public static function IsSuperuserOrMe(User $user, int $id): bool
    {
        return ($user->is_superuser ? true : false) or ($user->id === $id ? true : false);
    }

    public static function IsSuperuserOrMyPet(User $user, int $petId): bool
    {
        return ($user->is_superuser ? true : false) or ($user->id === Pet::find($petId)->user_id ? true : false);
    }

    public static function IsSuperuserOrMyScheduling(User $user, int $schedulingId): bool
    {
        return ($user->is_superuser ? true : false) or ($user->id === Scheduling::find($schedulingId)->user_id ? true : false);
    }
}