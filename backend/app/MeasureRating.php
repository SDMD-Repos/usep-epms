<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class MeasureRating extends Model
{
    use SoftDeletes;

    /**
     * Get the items for the ratings.
     */

    public function items()
    {
        return $this->hasMany('App\MeasureItem', 'id', 'rating')->orderBy('rating', 'desc');
    }

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        static::creating(function($model) {
            $model->setCreateAttributes();
        });

        static::updating(function($model) {
            $model->setUpdateAttributes();
        });
    }

    # SETTERS
    /**
     * Set attributes related to create method.
     *
     * @return void
     */
    protected function setCreateAttributes()
    {
        $this->attributes['create_id'] = Auth::user()->pmaps_id;
    }

    /**
     * Set attributes related to update method.
     *
     * @return void
     */
    protected function setUpdateAttributes()
    {
        $this->attributes['modify_id'] = Auth::user()->pmaps_id;
        $this->attributes['updated_at'] = Carbon::now();
    }
}
