<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OpcrTemplateDetail extends Model
{
    //
    use SoftDeletes;

    public function aapcr()
    {
        return $this->belongsTo('App\Models\OpcrTemplate');
    }

    /**
     * Get the sub category of the detail.
     */
    public function subCategory()
    {
        return $this->belongsTo('App\Models\SubCategory');
    }

    /**
     * Get the category of the detail.
     */
    public function category()
    {
        return $this->belongsTo('App\Models\Category');
    }

    /**
     * Get the measures of the detail.
     */
    public function measures()
    {
        return $this->belongsToMany('App\Models\Measure', 'opcr_template_detail_measures', 'detail_id', 'measure_id')
            ->using('App\Models\OpcrTemplateDetailMeasure')
            ->withPivot('category_id')
            ->wherePivotNull('deleted_at')->orderBy('id', 'ASC');
    }

    /**
     * Get the program of the detail.
     */
    public function program()
    {
        return $this->belongsTo('App\Models\Program');
    }

    public function mainDetails()
    {
        return $this->hasMany("App\Models\OpcrTemplateDetail", 'parent_id');
    }

    public function subDetails()
    {
        return $this->mainDetails()->with('subDetails');
    }

    public function parent()
    {
        return $this->belongsTo('App\Models\AapcrDetail', 'parent_id');
    }

}
