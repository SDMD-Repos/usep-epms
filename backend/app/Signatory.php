<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Signatory extends Model
{
    use SoftDeletes;

    /**
     * Get the type of the signatory.
     */

    public function type()
    {
        return $this->belongsTo('App\SignatoryType', 'type_id');
    }
}
