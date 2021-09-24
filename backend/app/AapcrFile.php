<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AapcrFile extends Model
{
    use SoftDeletes;

    /**
     * Get the aapcr that owns the file.
     */

    public function aapcr()
    {
        return $this->belongsTo('App\Aapcr', 'form_id');
    }
}