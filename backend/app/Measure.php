<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Measure extends Model
{
    use SoftDeletes;

    /**
     * Get the items for the measure.
     */

    public function items()
    {
        return $this->hasMany('App\MeasureItem')->orderBy('rate', 'asc');
    }

    public static function boot() {
        parent::boot();

        static::deleting(function($measure) {
            foreach($measure->items as $item) {
                $loginUser = Auth::user()->pmaps_id;

                $item->modify_id = $loginUser;
                $item->updated_at = Carbon::now();
                $item->history = $item->history . "Deleted " . Carbon::now() . " by " . $loginUser . "\n";

                $item->save();

                $item->delete();
            }

        });
    }
}
