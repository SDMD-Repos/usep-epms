<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FormFieldSetting extends Model
{
    use SoftDeletes;

    /**
     * Get the form field that owns the settings.
     */
    public function formField()
    {
        return $this->belongsTo('App\Models\FormField');
    }
}
