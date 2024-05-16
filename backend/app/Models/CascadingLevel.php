<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CascadingLevel extends Model
{
    use SoftDeletes;

    /**
     * Get the AAPCR details of the cascading level.
     */
    public function aapcrDetails()
    {
        return $this->hasMany('App\Models\AapcrDetails');
    }
}
