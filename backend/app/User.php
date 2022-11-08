<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [

    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $keyType = 'string';

    public $incrementing = false;

    public function accessrights()
    {
        return $this->belongsToMany('App\AccessRight', 'user_access_rights', 'user_id', 'access_right_id')
            ->wherePivotNull('deleted_at')->orderBy('id', 'ASC');
    }

    public function getFullnameAttribute()
    {
        return $this->firstName . " " . $this->lastName;
    }

}
