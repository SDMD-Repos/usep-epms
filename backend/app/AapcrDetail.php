<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AapcrDetail extends Model
{
    use SoftDeletes;

    /**
     * Get the aapcr that owns the detail.
     */

    public function aapcr()
    {
        return $this->belongsTo('App\Aapcr');
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
        return $this->belongsToMany('App\Measure', 'aapcr_detail_measures', 'detail_id', 'measure_id')
            ->wherePivotNull('deleted_at')->orderBy('id', 'ASC');
    }

    /**
     * Get the offices of the detail.
     */
    public function offices()
    {
        return $this->hasMany('App\AapcrDetailOffice', 'detail_id');
    }

    public function getMeasureIds()
    {
        return $this->measures()->allRelatedIds();
    }

    public function mainDetails()
    {
        return $this->hasMany("App\AapcrDetail", 'parent_id');
    }

    public function subDetails()
    {
        return $this->mainDetails()->with('subDetails');
    }
}
