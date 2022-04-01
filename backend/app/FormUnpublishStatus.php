<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FormUnpublishStatus extends Model
{
    use SoftDeletes;

    /**
     * Get the form that owns the status.
     */

    public function form()
    {
        return $this->belongsTo('App\Form', 'form_type');
    }
}
