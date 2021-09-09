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
            'effectivity' => 'required|date|after:created_at',
            'members' => 'required|array',
            'hasChair' => 'boolean',
            'chairId' => 'exclude_if:hasChair,false|required',
            'chairOffice' => 'exclude_if:hasChair,false|required',
            'deleted' => 'array'
        ];
    }
}
