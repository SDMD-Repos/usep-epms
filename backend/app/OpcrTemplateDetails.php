<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OpcrTemplateDetails extends Model
{
    //
    use SoftDeletes;

    public function aapcr()
    {
        return $this->belongsTo('App\OpcrTemplate');
    }

}
