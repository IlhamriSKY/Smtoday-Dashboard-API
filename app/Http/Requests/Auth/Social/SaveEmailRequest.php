<?php

namespace Vanguard\Http\Requests\Auth\Social;

use Vanguard\Http\Requests\Request;

class SaveEmailRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email' => 'required|email|unique:users,email',
        ];
    }
}
