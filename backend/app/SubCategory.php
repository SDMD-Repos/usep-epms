<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class SubCategory extends Model
{
    use SoftDeletes;

    public function category()
    {
        return $this->belongsTo('App\Category','category_id');
    }

    public function mainSubCategories()
    {
        return $this->hasMany("App\SubCategory", 'parent_id');
    }

    public function childSubCategories()
    {
        return $this->mainSubCategories()->with('childSubCategories');
    }

    public static function boot() {
        parent::boot();

        static::deleting(function($subcategory) {
            foreach($subcategory->childSubCategories as $child) {
                $loginUser = Auth::user()->pmaps_id;

                $child->modify_id = $loginUser;
                $child->updated_at = Carbon::now();
                $child->history = $child->history . "Deleted " . Carbon::now() . " by " . $loginUser . "\n";

                $child->save();

                $child->delete();

                $child->childSubCategories()->delete();
            }

        });
    }
}
