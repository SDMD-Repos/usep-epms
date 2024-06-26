<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use SoftDeletes;

    /**
     * The category that belong to the sub categories.
     */
    public function subCategory()
    {
        return $this->hasMany('App\Models\SubCategory');
    }

    /**
     * The category that belong to the programs.
     */
    public function programs()
    {
        return $this->hasMany('App\Models\Program');
    }

    /**
     * The category that belong to the AAPCR offices
     */
    public function aapcrOffices()
    {
        return $this->hasMany('App\Models\AapcrDetailOffice', 'cascade_to');
    }

    /**
     * The category that belong to the forms
     */
    public function formCategory()
    {
        return $this->hasOne('App\Models\FormCategory');
    }

    /**
     * The default program of the category
     */
    public function defaultProgram()
    {
        return $this->belongsTo('App\Models\Program', 'default_program_id', 'id');
    }

}
