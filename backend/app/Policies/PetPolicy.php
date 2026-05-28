<?php

namespace App\Policies;

use App\Models\Pet;

use App\Models\User;

class PetPolicy
{

    public function view(User $user, Pet $pet): bool
    {
        if ($user->hasRole('ADMIN')) {

            return true;
        }

        return $pet->user_id === $user->id;
    }

    public function update(User $user, Pet $pet): bool
    {
        if ($user->hasRole('ADMIN')) {

            return true;
        }

        return $pet->user_id === $user->id;
    }

    public function delete(User $user, Pet $pet): bool
    {
        if ($user->hasRole('ADMIN')) {

            return true;
        }

        return $pet->user_id === $user->id;
    }
}