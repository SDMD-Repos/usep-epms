<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AapcrDetailOffice extends Model
{
    use SoftDeletes;

    /**
     * Get the aapcr detail of the offices.
     */

    public function detail()
    {
        return $this->belongsTo('App\Models\AapcrDetail', 'detail_id');
    }

    /**
     * Get the group of the offices.
     */

    public function group()
    {
        return $this->belongsTo('App\Models\Group', 'group_id')->withTrashed();
    }

    /**
     * Get the field of the offices.
     */

    public function field()
    {
        return $this->belongsTo('App\Models\FormField', 'office_type_id');
    }

    /**
     * Get the category of the offices.
     */

    public function category()
    {
        return $this->belongsTo('App\Models\Category', 'cascade_to');
    }

    /**
     * Scope a query to only include users of a given type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  mixed  $id
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilterVpOffices($query, $id)
    {
        return $query->where(function ($officeQ) use ($id) {
            $officeQ->where('vp_office_id', '=', $id)
                ->orWhere('office_id', '=', $id)
                ->orWhere(function ($groupOfcWhere) use ($id) {
                    $groupOfcWhere->where('is_group', 1)
                        ->whereHas('group', function ($joinOfcQuery) use ($id) {
                            $joinOfcQuery->where('supervising_id', '=', $id);
                        });
                });
        });
    }

    public function getImplementingOfficeDetails($parentId)
    {

        // dd($parentId);
        return self::where('detail_id', $parentId)
            ->where('cascade_to', 1)
            ->where('office_type_id', 1)
            ->get();
    }


    public function getSupportOfficeDetails($parentId)
    {
        return self::where('detail_id', $parentId)
            ->where('cascade_to', 2)
            ->where('office_type_id', 2)
            ->get();
    }
}
