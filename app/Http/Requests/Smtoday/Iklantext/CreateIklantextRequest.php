<?php

namespace Vanguard\Http\Requests\Smtoday\Iklantext;

use Vanguard\Http\Requests\Request;
use Vanguard\Iklantext;

class CreateIklantextRequest extends Request
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
