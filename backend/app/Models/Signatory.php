<?php

namespace App\Models;

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
        return $this->belongsTo('App\Models\SignatoryType', 'type_id');
    }
}
