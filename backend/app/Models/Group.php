<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Group extends Model
{
    use SoftDeletes;

    /**
     * Get the memebers for the group.
     */
    public function members()
    {
        return $this->hasMany('App\Models\GroupMember');
    }

    public static function boot() {
        parent::boot();

        static::deleting(function($group) {
            foreach($group->members as $member) {
                $loginUser = Auth::user()->pmaps_id;

                $member->modify_id = $loginUser;
                $member->updated_at = Carbon::now();
                $member->history = $member->history . "Deleted " . Carbon::now() . " by " . $loginUser . "\n";

                $member->save();

                $member->delete();
            }

        });
    }
}
