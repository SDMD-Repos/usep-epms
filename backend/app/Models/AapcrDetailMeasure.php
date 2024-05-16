<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class AapcrDetailMeasure extends Pivot
{
    use SoftDeletes;

    protected $table = 'aapcr_detail_measures';

    /**
     * Get the measure of the detail.
     */

    public function measure()
    {
        return $this->belongsTo('App\Models\Measure');
    }

    /**
     * Get the measure of the detail.
     */

    public function category()
    {
        return $this->belongsTo('App\Models\MeasureCategory');
    }

}
