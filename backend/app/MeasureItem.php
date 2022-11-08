<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MeasureItem extends Model
{
    use SoftDeletes;

    public function measure(){
        return $this->belongsTo('App\Measure','id', 'measure_id');
    }

    public function category(){
        return $this->belongsTo('App\MeasureCategory','id', 'category_id');
    }

    public function ratingEnd(){
        return $this->belongsTo('App\MeasureRating','rating', 'id');
    }

    public function rating(){
        return $this->ratingEnd()->select( 'id', 'numerical_rating');
    }
}
