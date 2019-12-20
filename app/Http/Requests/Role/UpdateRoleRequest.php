<?php

namespace Vanguard\Http\Requests\Role;

use Vanguard\Http\Requests\Request;

class UpdateRoleRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $role = $this->route('role');

        return [
            'name' => 'required|regex:/^[a-zA-Z0-9\-_\.]+$/|unique:roles,name,' . $role->id
        ];
    }
}
