<?php

namespace Vanguard\Http\Requests\Smtoday\Beritatext;

use Vanguard\Http\Requests\Request;
use Vanguard\Beritatext;

class CreateBeritatextRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'judul' => 'required',
            'text' => 'required',
            'verified' => 'boolean'
        ];

        return $rules;
    }
}
