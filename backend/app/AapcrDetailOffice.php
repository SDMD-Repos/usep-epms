<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AapcrDetailOffice extends Model
{
    use SoftDeletes;

    /**
     * Get the aapcr detail of the offices.
     */

    public function detail()
    {
        return $this->belongsTo('App\AapcrDetail', 'detail_id');
    }

    public function group()
    {
        return $this->belongsTo('App\Group', 'group_id')->withTrashed();
    }
}
