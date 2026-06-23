<?php

namespace App\Policies;

use App\Models\Tracer\CareerHistory;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CareerHistoryPolicy
{
    use HandlesAuthorization;

    private function getRole(): string
    {
        return request()->attributes->get('resolved_role', session('active_role')) ?? '';
    }

    public function viewAny(User $user): bool
    {
        return in_array($this->getRole(), ['alumni', 'admin']);
    }

    public function view(User $user, CareerHistory $career): bool
    {
        return $this->getRole() === 'admin' ||
               ($user->alumniProfile && $user->alumniProfile->id === $career->profil_alumni_id);
    }

    public function create(User $user): bool
    {
        return $this->getRole() === 'alumni' && $user->alumniProfile !== null;
    }

    public function update(User $user, CareerHistory $career): bool
    {
        return $user->alumniProfile &&
               $user->alumniProfile->id === $career->profil_alumni_id;
    }

    public function delete(User $user, CareerHistory $career): bool
    {
        return $user->alumniProfile &&
               $user->alumniProfile->id === $career->profil_alumni_id;
    }
}
