<?php

namespace App\Policies;

use App\Models\Tracer\CareerHistory;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CareerHistoryPolicy
{
    use HandlesAuthorization;

    private function getRole(User $user): string
    {
        return $user->getResolvedRoleSlug() ?? '';
    }

    public function viewAny(User $user): bool
    {
        return in_array($this->getRole($user), ['alumni', 'admin']);
    }

    public function view(User $user, CareerHistory $career): bool
    {
        return $this->getRole($user) === 'admin' ||
               ($user->alumniProfile && $user->alumniProfile->id === $career->profil_alumni_id);
    }

    public function create(User $user): bool
    {
        return $this->getRole($user) === 'alumni' && $user->alumniProfile !== null;
    }

    public function update(User $user, CareerHistory $career): bool
    {
        return $this->getRole($user) === 'alumni' &&
               $user->alumniProfile &&
               $user->alumniProfile->id === $career->profil_alumni_id;
    }

    public function delete(User $user, CareerHistory $career): bool
    {
        return $this->getRole($user) === 'alumni' &&
               $user->alumniProfile &&
               $user->alumniProfile->id === $career->profil_alumni_id;
    }
}
