<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VpOpcrDetail extends Model
{
    use SoftDeletes;

    /**
     * Get the vp opcr that owns the detail.
     */
    public function vpopcr()
    {
        return $this->belongsTo('App\VpOpcr');
    }

    public function mainDetails()
    {
        return $this->hasMany("App\VpOpcrDetail", 'parent_id');
    }

    public function subDetails()
    {
        return $this->mainDetails()->with('subDetails');
    }

    /**
     * Get the program of the detail.
     */
    public function program()
    {
        return $this->belongsTo('App\Program');
    }

    /**
     * Get the sub category of the detail.
     */
    public function subCategory()
    {
        return $this->belongsTo('App\SubCategory');
    }

    /**
     * Get the category of the detail.
     */
    public function category()
    {
        return $this->belongsTo('App\Category');
    }

    /**
     * Get the measures of the detail.
     */
    public function measures()
    {
        return $this->belongsToMany('App\Measure', 'vp_opcr_detail_measures', 'detail_id', 'measure_id')
            ->wherePivotNull('deleted_at')->orderBy('id', 'ASC');
    }

    /**
     * Get the offices of the detail.
     */
    public function offices()
    {
        return $this->hasMany('App\VpOpcrDetailOffice', 'detail_id')->orderBy('office_type_id', 'asc');
    }

    public function aapcrDetail()
    {
        return $this->belongsTo('App\AapcrDetail', 'aapcr_detail_id');
    }

    /*public function getMeasureIds()
    {
        return $this->measures()->allRelatedIds();
    }*/
}
