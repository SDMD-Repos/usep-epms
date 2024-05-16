<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class VpOpcrDetail extends Model
{
    use SoftDeletes;

    /**
     * Get the vp opcr that owns the detail.
     */
    public function vpopcr()
    {
        return $this->belongsTo('App\Models\VpOpcr', 'vp_opcr_id');
    }

    public function mainDetails()
    {
        return $this->hasMany("App\Models\VpOpcrDetail", 'parent_id');
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
        return $this->belongsTo('App\Models\Program');
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
     * Get the cascading level of the detail.
     */
    public function cascading()
    {
        return $this->belongsTo('App\Models\CascadingLevel', 'cascading_level', 'code');
    }

    /**
     * Get the measures of the detail.
     */
    public function measures()
    {
        return $this->belongsToMany('App\Models\Measure', 'vp_opcr_detail_measures', 'detail_id', 'measure_id')
            ->using('App\Models\AapcrDetailMeasure')
            ->withPivot('category_id')
            ->wherePivotNull('deleted_at')->orderBy('id', 'ASC');
    }

    /**
     * Get the offices of the detail.
     */
    public function offices()
    {
        return $this->hasMany('App\Models\VpOpcrDetailOffice', 'detail_id')->orderBy('office_type_id', 'asc');
    }

    public function aapcrDetail()
    {
        return $this->belongsTo('App\Models\AapcrDetail', 'aapcr_detail_id');
    }

    public function cascadingLevel()
    {
        return $this->belongsTo('App\Models\CascadingLevel');
    }

    public static function boot() {
        parent::boot();

        static::deleting(function($details) {
            foreach($details->subDetails as $sub) {
                $fullName = Auth::user()->firstName . " " . Auth::user()->lastName;

                $sub->modify_id = Auth::user()->pmaps_id;
                $sub->updated_at = Carbon::now();
                $sub->history = $sub->history . "Deleted " . Carbon::now() . " by " . $fullName . "\n";

                $sub->save();

                $sub->delete();
            }

        });
    }
}
