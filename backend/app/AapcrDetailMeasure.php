<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class AapcrDetailMeasure extends Pivot
{
    use SoftDeletes;

    /**
     * Get the measure of the detail.
     */

    public function measure()
    {
        return $this->belongsTo('App\Measure');
    }

    /**
     * Get the measure of the detail.
     */

    public function category()
    {
        return $this->belongsTo('App\MeasureCategory');
    }

}
