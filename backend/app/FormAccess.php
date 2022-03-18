<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FormAccess extends Model
{
    //
    protected $fillable = ['form_id', 'pmaps_id','office_id','create_id','office_name','pmaps_name','staff_id','staff_name'];


    protected $table = "form_access";
}
