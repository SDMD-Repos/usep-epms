<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AapcrProgramBudget extends Model
{
    use SoftDeletes;

    /**
     * Get the program of the budget.
     */

    public function program()
    {
        return $this->belongsTo('App\Program');
    }

    /**
     * Get the aapcr of the budget.
     */

    public function aapcr()
    {
        return $this->belongsTo('App\Aapcr');
    }
}
