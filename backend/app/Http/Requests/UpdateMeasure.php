<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMeasure extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'id' => '',
            'year' => 'required',
            'name' => 'required',
            'displayAsItems' => 'boolean',
            'isCustom' => 'boolean',
            'description' => 'required_if:is_custom,false',
            'variableEquivalent' => 'required_if:is_custom,false',
            'elements' => 'required_if:is_custom,false',
            'bgColor' => '',
            'categories' => 'required_if:is_custom,false',
            'customItems' => 'required_if:is_custom,true',
            'deleted' => '',
        ];
    }
}
