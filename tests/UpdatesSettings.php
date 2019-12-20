<?php

namespace Tests;

use Setting;

trait UpdatesSettings
{
    /**
     * Set some setting that should automatically be
     * reverted to it's default value after each test.
     * @param array $settings
     */
    protected function setSettings(array $settings)
    {
        foreach ($settings as $key => $value) {
            Setting::set($key, $value);
        }

        Setting::save();
    }
}
