<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class RechargeSave extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'recharge_amount' => 'required|numeric|max:1000'
        ];
    }

    public function messages()
    {
        return [
            'recharge_amount.max' => __('The recharge amount exceeds the limit')
        ];
    }
}
