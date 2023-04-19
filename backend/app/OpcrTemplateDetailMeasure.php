<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class OpcrTemplateDetailMeasure extends Pivot
{
    //
    use SoftDeletes;

    protected $table = 'opcr_template_detail_measures';

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
