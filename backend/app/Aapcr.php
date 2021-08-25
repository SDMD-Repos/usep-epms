<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Aapcr extends Model
{
    use SoftDeletes;

    /**
     * Get the details for the aapcr.
     */

    public function details()
    {
        return $this->hasMany('App\AapcrDetail');
    }

    /**
     * Get the details for the aapcr with null parent_pi_id.
     */

    public function detailParents()
    {
        return $this->details()->whereNull('parent_id');
    }


    /**
     * Get the details for the aapcr
     *  ordered by category_id ASC, program_id ASC, and sub_category_id ASC
     */

    public function detailsOrdered()
    {
        return $this->details()
            ->orderBy('category_id', 'ASC')
            ->orderBy('program_id', 'ASC')
            ->orderBy('sub_category_id', 'ASC');
    }

    /**
     * Get the budgets for the aapcr.
     */

    public function budgets()
    {
        return $this->hasMany('App\AapcrProgramBudget');
    }

    /**
     * Get the files for the aapcr.
     */

    public function files()
    {
        return $this->hasMany('App\AapcrFile');
    }
}
