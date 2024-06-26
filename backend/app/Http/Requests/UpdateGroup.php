<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGroup extends FormRequest
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
            'name' => ['required', Rule::unique('groups')->ignore($this->id)->whereNull('deleted_at')],
            'effectivity' => 'required|digits:4',
            'supervising' => 'required',
            'members' => 'required|array',
            'hasChair' => 'boolean',
            'chair.id' => 'exclude_if:hasChair,false|required',
            'chair.office' => 'exclude_if:hasChair,false|required',
            'chair.isSubunit' => 'exclude_if:hasChair,false|required',
            'deleted' => 'array'
        ];
    }
}
