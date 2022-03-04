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
}
