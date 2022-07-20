<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Program extends Model
{
    use SoftDeletes;

    public function category()
    {
        return $this->belongsTo('App\Category','category_id');
    }

    public function defaultCategory()
    {
        return $this->hasMany('App\Category','default_program_id', 'id');
    }
}
