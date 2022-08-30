<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSignatory extends FormRequest
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
            'signatories' => 'required|array|max:3',
            'year' => 'required',
            'typeId' => 'required',
            'formId' => 'required',
            'position' => '',
            'officeId' => 'required_if:formId,vpopcr,opcr'
        ];
    }
}
