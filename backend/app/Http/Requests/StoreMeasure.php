<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMeasure extends FormRequest
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
            'year' => 'required',
            'measures.*.name' => 'required',
            'measures.*.displayAsItems' => 'boolean',
            'measures.*.isCustom' => 'boolean',
            'measures.*.description' => 'required_if:is_custom,false',
            'measures.*.variableEquivalent' => 'required_if:is_custom,false',
            'measures.*.elements' => 'required_if:is_custom,false',
            'measures.*.categories' => 'required_if:is_custom,false',
            'measures.*.customItems' => 'required_if:is_custom,true',
        ];
    }
}
