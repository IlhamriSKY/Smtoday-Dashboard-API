<?php

namespace Vanguard\Http\Requests\TwoFactor;

class EnableTwoFactorRequest extends TwoFactorRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'country_code' => 'required|numeric|integer',
            'phone_number' => 'required|numeric',
        ];
    }
}
