<?php

namespace App;

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
        return $this->hasMany('App\OtherProgram');
    }

    public function formAccess()
    {
        return $this->hasMany('App\FormAccess', 'form_id');
    }

    /**
     * Get the status for the form.
     */

    public function unpublishStatus()
    {
        return $this->hasMany('App\FormUnpublishStatus', 'form_type');
    }

}
