<?php

namespace App\Policies;

use App\Models\{User, Project};

class ProjectPolicy
{
    public function viewAny(User $user): bool { return true; }
    public function view(?User $user, Project $project): bool { return true; }

    public function create(User $user): bool
    {
        return $user->isProjectOwner() || $user->isAdmin();
    }

    public function update(User $user, Project $project): bool
    {
        return $user->isAdmin() || $user->id === $project->owner_id;
    }

    public function delete(User $user, Project $project): bool
    {
        return $user->isAdmin() || ($user->id === $project->owner_id && $project->status === 'pending');
    }

    public function approve(User $user): bool { return $user->isAdmin(); }
    public function reject(User $user): bool  { return $user->isAdmin(); }
}