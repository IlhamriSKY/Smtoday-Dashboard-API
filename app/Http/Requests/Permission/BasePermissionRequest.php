<?php

namespace Vanguard\Http\Requests\Permission;

use Illuminate\Foundation\Http\FormRequest;

class BasePermissionRequest extends FormRequest
{
    /**
     * Validation messages.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'name.unique' => __('Permission with this name already exists.')
        ];
    }
}
