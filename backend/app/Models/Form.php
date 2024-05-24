<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Form extends Model
{
    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * The category that belong to the opcrOtherPrograms.
     */
    public function programs()
    {
        return $this->hasMany('App\Models\OtherProgram');
    }

    public function formAccess()
    {
        return $this->hasMany('App\Models\FormAccess', 'form_id');
    }

    /**
     * Get the status for the form.
     */

    public function unpublishStatus()
    {
        return $this->hasMany('App\Models\FormUnpublishStatus', 'form_type');
    }

}
