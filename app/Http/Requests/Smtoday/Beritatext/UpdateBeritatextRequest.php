<?php

namespace Vanguard\Http\Requests\Smtoday\Beritatext;

use Illuminate\Foundation\Http\FormRequest;

use Vanguard\Http\Requests\Request;
use Vanguard\Beritatext;

class UpdateBeritatextRequest extends Request
{
   /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        //$Beritatext = $this->Beritatext();

        return [
            'judul' => 'required',
            'text' => 'required'
        ];
    }
}
