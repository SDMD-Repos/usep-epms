<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccessRight extends Model
{
    protected $fillable = ['user_id'];
    use SoftDeletes;

}
