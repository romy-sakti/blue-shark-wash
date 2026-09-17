<?php

namespace App\Policies;

use App\Models\BookkeepingPeriod;
use App\Models\User;

class BookkeepingPeriodPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, BookkeepingPeriod $period): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, BookkeepingPeriod $period): bool
    {
        return ! $period->isLocked() || $user->isOwner();
    }

    public function delete(User $user, BookkeepingPeriod $period): bool
    {
        return ! $period->isLocked() || $user->isOwner();
    }

    public function lock(User $user, BookkeepingPeriod $period): bool
    {
        return $user->isOwner();
    }

    public function unlock(User $user, BookkeepingPeriod $period): bool
    {
        return $user->isOwner();
    }
}
