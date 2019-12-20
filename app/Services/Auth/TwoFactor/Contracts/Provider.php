<?php

namespace Vanguard\Services\Auth\TwoFactor\Contracts;

use Vanguard\Services\Auth\TwoFactor\Contracts\Authenticatable as TwoFactorAuthenticatable;

interface Provider
{
    /**
     * Determine if the given user has two-factor authentication enabled.
     *
     * @param Authenticatable $user
     * @return bool
     */
    public function isEnabled(TwoFactorAuthenticatable $user);

    /**
     * Register the given user with the provider.
     *
     * @param Authenticatable $user
     */
    public function register(TwoFactorAuthenticatable $user);

    /**
     * Sends an SMS with a phone verification token.
     * @param Authenticatable $user
     * @return mixed
     */
    public function sendTwoFactorVerificationToken(TwoFactorAuthenticatable $user);

    /**
     * Determine if the given token is valid for the given user.
     *
     * @param Authenticatable $user
     * @param  string $token
     * @return bool
     */
    public function tokenIsValid(TwoFactorAuthenticatable $user, $token);

    /**
     * Delete the given user from the provider.
     *
     * @param Authenticatable $user
     * @return bool
     */
    public function delete(TwoFactorAuthenticatable $user);
}
