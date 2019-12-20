<?php

namespace Vanguard\Http\Requests\Smtoday\Iklanimage;

use Vanguard\Http\Requests\Request;
use Vanguard\Iklanimage;

class CreateIklanimageRequest extends Request
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
            'verified' => 'boolean'
        ];

        return $rules;
    }
}
