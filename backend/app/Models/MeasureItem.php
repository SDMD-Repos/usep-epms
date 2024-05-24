<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MeasureItem extends Model
{
    use SoftDeletes;

    public function measure(){
        return $this->belongsTo('App\Models\Measure','id', 'measure_id');
    }

    public function category(){
        return $this->belongsTo('App\Models\MeasureCategory','id', 'category_id');
    }

    public function ratingEnd(){
        return $this->belongsTo('App\Models\MeasureRating','rating', 'id');
    }

    public function rating(){
        return $this->ratingEnd()->select( 'id', 'numerical_rating');
    }
}
