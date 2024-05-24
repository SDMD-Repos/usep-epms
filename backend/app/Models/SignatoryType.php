<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SignatoryType extends Model
{
    /**
     * Get the aapcr detail for the form field.
     */

    public function signatory()
    {
        return $this->hasMany('App\Models\Signatory', 'type_id');
    }
}
