<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OpcrTemplate extends Model
{
    use SoftDeletes;

    /**
     * Get the details for the Opcr Template.
     */

    protected $table = 'opcr_template';

    public function details()
    {
        return $this->hasMany('App\OpcrTemplateDetails');
    }
}
