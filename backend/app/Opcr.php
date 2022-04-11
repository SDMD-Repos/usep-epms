<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Opcr extends Model
{
    use SoftDeletes;

    /**
     * Get the details for the Opcr Template.
     */

    protected $table = 'opcr';

    public function details()
    {
        return $this->hasMany('App\OpcrDetails');
    }

    /**
     * Get the details for the opcr template with null parent_pi_id.
     */

    public function detailParents()
    {
        return $this->details()->whereNull('parent_id');
    }

    public function detailsOrdered()
    {
        return $this->details()
            ->orderBy('category_id', 'ASC')
            ->orderBy('program_id', 'ASC')
            ->orderBy('sub_category_id', 'ASC');
    }

}
