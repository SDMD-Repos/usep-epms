<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OtherProgram extends Model
{
    use SoftDeletes;

    protected $table = 'other_programs';

    public function category()
    {
        return $this->belongsTo('App\Models\Category','category_id');
    }

    public function forms()
    {
        return $this->belongsTo('App\Models\Category','category_id');
    }
}
