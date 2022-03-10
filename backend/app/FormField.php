<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FormField extends Model
{
    use SoftDeletes;

    /**
     * Get the settings for the form field.
     */

    public function settings()
    {
        return $this->hasOne('App\FormFieldSetting', 'field_id');
    }

    /**
     * Get the aapcr detail for the form field.
     */

    public function aapcrDetail()
    {
        return $this->hasMany('App\AapcrDetail', 'office_type_id');
    }

    /**
     * Get the aapcr detail for the form field.
     */

    public function opcrTemplateDetail()
    {
        return $this->hasMany('App\OpcrTemplateDetail', 'office_type_id');
    }
}
