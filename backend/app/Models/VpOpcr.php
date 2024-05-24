<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VpOpcr extends Model
{
    use SoftDeletes;

    /**
     * Get the details for the vp opcr
     *  ordered by category_id ASC, program_id ASC, and sub_category_id ASC
     */

    public function detailsOrdered()
    {
        return $this->hasMany('App\Models\VpOpcrDetail')
            ->orderBy('category_id', 'ASC')
            ->orderBy('program_id', 'ASC')
            ->orderBy('sub_category_id', 'ASC');
    }

    /**
     * Get the details for the vp opcr
     *  ordered by category_id ASC
     */

    public function details()
    {
        return $this->hasMany('App\Models\VpOpcrDetail')->orderBy('category_id', 'ASC');
    }

    /**
     * Get the details for the vp opcr with null parent_id.
     */

    public function detailParents()
    {
        return $this->details()->whereNull('parent_id')->orderByRaw('-`aapcr_detail_id` DESC');
    }

    /**
     * Get the form unpublish status of the vp's opcr.
     */

    public function status()
    {
        return $this->hasMany('App\Models\FormUnpublishStatus', 'form_id', 'id')
            ->where('form_type', '=', 'vpopcr');
    }
}
