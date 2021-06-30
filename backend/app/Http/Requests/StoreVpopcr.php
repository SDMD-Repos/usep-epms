<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVpopcr extends FormRequest
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
            'dataSource' => 'required|array',
            'fiscalYear' => 'required|digits:4',
            'isFinalized' => 'boolean',
            'vpOffice' => 'required',
            'aapcrId' => 'numeric'
        ];
    }
}
