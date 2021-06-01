<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use SoftDeletes;
    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * The category that belong to the aapcrSubCategories.
     */
    public function subCategory()
    {
        return $this->hasMany('App\SubCategory');
    }

    /**
     * The category that belong to the aapcrPrograms.
     */
    public function programs()
    {
        return $this->hasMany('App\Program');
    }
}
