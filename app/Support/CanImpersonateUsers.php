<?php

namespace Vanguard\Support;

trait CanImpersonateUsers
{
    /**
     * Check if a user can impersonate other users.
     *
     * @return bool
     */
    public function canImpersonate()
    {
        return $this->hasPermission('users.manage');
    }

    /**
     * Check if a target user can be impersonated.
     * By default, all users can be impersonated if a currently logged
     * user is not already impersonating another user.
     *
     * @return bool
     */
    public function canBeImpersonated()
    {
        return ! app('impersonate')->isImpersonating();
    }
}
