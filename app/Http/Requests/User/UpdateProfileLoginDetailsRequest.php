<?php

namespace Vanguard\Http\Requests\User;

class UpdateProfileLoginDetailsRequest extends UpdateLoginDetailsRequest
{
    /**
     * Get authenticated user.
     *
     * @return mixed
     */
    protected function getUserForUpdate()
    {
        return \Auth::user();
    }
}
