<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMeasureRating extends FormRequest
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
        $rules = [
            'year' => 'required',
            'ratings.*.year' => 'required',
            'ratings.*.aps_from' => 'required',
            'ratings.*.aps_to' => 'required',
            'ratings.*.adjectival_rating' => 'required',
            'ratings.*.description' => 'required',
            'ratings.*.numerical_rating' => 'required|integer|digits_between:1,10'
        ];

        return $rules;
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'ratings.*.numerical_rating' => 'Unable to save data. A same Numerical Rating has already been set',
        ];
    }
}
