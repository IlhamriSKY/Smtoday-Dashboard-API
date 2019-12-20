<?php

namespace Vanguard\Services\Auth\TwoFactor;

trait Authenticatable
{
    /**
     * Get the e-mail address used for two-factor authentication.
     *
     * @return string
     */
    public function getEmailForTwoFactorAuth()
    {
        return $this->email;
    }

    /**
     * Get the country code used for two-factor authentication.
     *
     * @return string
     */
    public function getAuthCountryCode()
    {
        return $this->two_factor_country_code;
    }

    /**
     * Get the phone number used for two-factor authentication.
     *
     * @return string
     */
    public function getAuthPhoneNumber()
    {
        return $this->two_factor_phone;
    }

    /**
     * Set the country code and phone number used for two-factor authentication.
     *
     * @param $countryCode
     * @param  string $phoneNumber
     */
    public function setAuthPhoneInformation($countryCode, $phoneNumber)
    {
        $this->two_factor_country_code = $countryCode;
        $this->two_factor_phone = $phoneNumber;
    }

    /**
     * Get the two-factor provider options in array format.
     *
     * @return array
     */
    public function getTwoFactorAuthProviderOptions()
    {
        return json_decode($this->two_factor_options, true) ?: [];
    }

    /**
     * Set the two-factor provider options in array format.
     *
     * @param  array  $options
     * @return void
     */
    public function setTwoFactorAuthProviderOptions(array $options)
    {
        $this->two_factor_options = json_encode($options);
    }

    /**
     * Determine if the user is using two-factor authentication.
     *
     * @return bool
     */
    public function getUsingTwoFactorAuthAttribute()
    {
        $options = $this->getTwoFactorAuthProviderOptions();

        return isset($options['id']);
    }
}