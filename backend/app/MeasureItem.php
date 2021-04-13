<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MeasureItem extends Model
{
    use SoftDeletes;

    public function measure()
    {
        return $this->belongsTo('App\Measure','measure_id');
    }
}
