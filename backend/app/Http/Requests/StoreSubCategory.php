<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSubCategory extends FormRequest
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
            'name' => [
                'required',
                'max:100',
                Rule::unique('sub_categories')->whereNull('deleted_at')->where(function ($query) {
                    return $query->where('name', $this->name)->where('category_id', $this->category_id);
                })
            ],
            'category_id' => 'required',
            'parentId' => '',
            'year'=>'',
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
            'name.required' => 'This field is required',
            'name.max' => 'This field may not be greater than :max characters',
            'name.unique' => 'The Sub Category name has already been used.',
            'category_id.required' => 'This field is required',
        ];
    }

}
