<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OpcrTemplateDetailsMeasures extends Model
{
    //
    use SoftDeletes;

    /**
     * Get the measure of the detail.
     */

    public function measure()
    {
        return $this->belongsTo('App\Measure');
    }

}
