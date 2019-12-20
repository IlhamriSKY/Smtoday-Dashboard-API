<?php

namespace Vanguard\UserActivity\Http\Requests\Activity;

use Vanguard\Http\Requests\Request;

class GetActivitiesRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'per_page' => 'integer|max:100'
        ];
    }

    /**
     * @return array
     */
    public function messages()
    {
        return [
            'per_page.max' => __('Maximum number of records per page is 100.')
        ];
    }
}
