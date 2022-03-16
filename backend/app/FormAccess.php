<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FormAccess extends Model
{
    //
    protected $fillable = ['form_id', 'pmaps_id','create_id'];
    protected $table = "form_access";
}
