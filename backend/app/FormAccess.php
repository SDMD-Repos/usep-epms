<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FormAccess extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'form_id', 'pmaps_id', 'pmaps_name', 'office_id', 'staff_id',
        'create_at', 'create_id', 'updated_at', 'modify_id', 'deleted_at'
    ];

    protected $table = "form_access";

    public function form()
    {
        return $this->belongsTo('App\Form', 'form_id');
    }
}
