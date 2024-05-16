<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VpOpcrDetailMeasure extends Model
{
    use SoftDeletes;

    /**
     * Get the measure of the detail.
     */
    public function measure()
    {
        return $this->belongsTo('App\Models\Measure');
    }
}
