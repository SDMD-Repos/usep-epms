<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VpOpcrFile extends Model
{
    use SoftDeletes;

    /**
     * Get the vp opcr that owns the file.
     */

    public function vpopcr()
    {
        return $this->belongsTo('App\VpOpcr', 'form_id');
    }
}
