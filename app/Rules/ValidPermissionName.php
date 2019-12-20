<?php

namespace Vanguard\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidPermissionName implements Rule
{
    protected $regex = '/^[a-zA-Z0-9\-_\.]+$/';

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if (! is_string($value) && ! is_numeric($value)) {
            return false;
        }

        return preg_match($this->regex, $value) > 0;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('validation.regex', ['attribute' => __('permission name')]);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf("regex:%s", $this->regex);
    }
}
