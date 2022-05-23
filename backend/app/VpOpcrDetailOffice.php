<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VpOpcrDetailOffice extends Model
{
    use SoftDeletes;

    public function group()
    {
        return $this->belongsTo('App\Group', 'group_id');
    }

    /**
     * Get the field of the offices.
     */

    public function field()
    {
        return $this->belongsTo('App\FormField', 'office_type_id');
    }

    /**
     * Get the category of the office.
     */
    public function category()
    {
        return $this->belongsTo('App\Category','category_id');
    }

    /**
     * Get the program of the office.
     */
    public function program()
    {
        return $this->belongsTo('App\Program');
    }

    /**
     * Get the other program of the office.
     */
    public function otherProgram()
    {
        return $this->belongsTo('App\OtherProgram');
    }
}
