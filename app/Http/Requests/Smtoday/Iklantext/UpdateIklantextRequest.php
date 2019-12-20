<?php

namespace Vanguard\Http\Requests\Smtoday\Iklantext;

use Illuminate\Foundation\Http\FormRequest;

use Vanguard\Http\Requests\Request;
use Vanguard\Support\Enum\IklantextStatus;
use Vanguard\Iklantext;

class UpdateIklantextRequest extends Request
{
   /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        //$iklantext = $this->iklantext();

        return [
            'judul' => 'string',
            'text' => '',
        ];
    }
}
