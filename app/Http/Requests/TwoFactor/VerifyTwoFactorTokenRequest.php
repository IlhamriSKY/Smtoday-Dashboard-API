<?php

namespace Vanguard\Http\Requests\TwoFactor;

class VerifyTwoFactorTokenRequest extends TwoFactorRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'token' => 'required'
        ];
    }
}
