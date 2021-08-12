<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AapcrFile extends Model
{
    use SoftDeletes;

    /**
     * Get the aapcr that owns the detail.
     */

    public function aapcr()
    {
        return $this->belongsTo('App\Aapcr');
    }
}
