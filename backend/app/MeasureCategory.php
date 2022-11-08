<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MeasureCategory extends Model
{
    use SoftDeletes;

    public function measure()
    {
        return $this->belongsTo('App\Measure','measure_id');
    }

    /**
     * Get the items for the categories.
     */

    public function items()
    {
        return $this->hasMany('App\MeasureItem', 'category_id', 'id');
    }
}
