<?php

namespace App\Policies;

use App\Models\User;

use App\Models\Service;

class ServicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('ADMIN');
    }

    public function view(User $user, Service $service): bool
    {
        return $user->hasRole('ADMIN');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('ADMIN');
    }

    public function update(User $user, Service $service): bool
    {
        return $user->hasRole('ADMIN');
    }

    public function delete(User $user, Service $service): bool
    {
        return $user->hasRole('ADMIN');
    }

    public function restore(User $user, Service $service): bool
    {
        return $user->hasRole('ADMIN');
    }
}