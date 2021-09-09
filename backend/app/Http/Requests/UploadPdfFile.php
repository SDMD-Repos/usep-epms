<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadPdfFile extends FormRequest
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
            'files' => 'required',
            'files.*' => 'mimes:pdf|max:5120',
            'id' => 'required|integer'
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'files.*.required' => 'Please upload a file',
            'files.*.mimes' => 'Only pdf files are allowed',
            'files.*.max' => 'Sorry! Maximum allowed size for a file is 5MB',
            'id.required' => 'ID is required',
            'id.integer' => 'ID should be an integer'
        ];
    }
}
