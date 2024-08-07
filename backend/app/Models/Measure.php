<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Measure extends Model
{
    use SoftDeletes;

    /**
     * Get the custom items for the measure.
     */

    public function customItems()
    {
        return $this->hasMany('App\Models\MeasureItem', 'measure_id', 'id')->whereNull('category_id');
    }

    /**
     * Get the items for the measure.
     */

    public function items()
    {
        return $this->hasMany('App\Models\MeasureItem', 'measure_id', 'id')->orderBy('rating', 'asc');
    }

    /**
     * Get the categories for the measure.
     */

    public function categories()
    {
        return $this->hasMany('App\Models\MeasureCategory')->with('items')->orderBy('numbering', 'asc');
    }

    public function aapcrDetail() {
        return $this->belongsToMany('App\Models\AapcrDetail', 'aapcr_detail_measures', 'detail_id', 'measure_id')
            ->using('App\Models\AapcrDetailMeasure');
    }

    public function opcrTemplateDetail() {
        return $this->belongsToMany('App\Models\OpcrTemplateDetail', 'opcr_template_detail_measures', 'detail_id', 'measure_id')
            ->using('App\Models\OpcrTemplateDetailMeasure');
    }

    public static function boot() {
        parent::boot();

        static::deleting(function($measure) {
            $loginUser = Auth::user()->fullname;

            if(!$measure->is_custom) {
                foreach($measure->categories as $category) {
                    $category->modify_id = $loginUser;
                    $category->updated_at = Carbon::now();
                    $category->history = $category->history . "Deleted " . Carbon::now() . " by " . $loginUser . "\n";

                    $category->save();

                    $category->delete();

                    self::deleteItems($category->items);
                }
            }else {
                self::deleteItems($measure->customItems);
            }
        });
    }

    protected static function deleteItems($items) {
        $userFullname = Auth::user()->fullname;

        foreach($items as $item) {
            $item->modify_id = $userFullname;
            $item->updated_at = Carbon::now();
            $item->history = $item->history . "Deleted " . Carbon::now() . " by " . $userFullname . "\n";

            $item->save();

            $item->delete();
        }
    }


}
