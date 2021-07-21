<?php

namespace App\Http\Controllers\Form;

use App\Http\Controllers\Controller;
use App\Http\Traits\OfficeTrait;
use App\VpOpcr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OcpcrController extends Controller
{
    use OfficeTrait;

    private $login_user;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

        $this->middleware(function ($request, $next) {
            $this->login_user = Auth::user();

            if ($this->login_user) {
                $this->login_user->fullName = $this->login_user->firstName . " " . $this->login_user->lastName;
            }

            return $next($request);
        });

    }

    public function getVpOpcrDetails($officeId, $year, $formId)
    {
        $vpOfficeId = $this->getOfficeParentId($officeId, $formId);

        if($formId === 'opcr' && $vpOfficeId === null) {
            return response()->json("Department selected has no VP office assigned. Please contact the administrator", 400);
        }

        $getPIs = VpOpcr::where([
            ['year', $year],
            ['is_active', 1]
        ])->whereNotNull('published_date')
            ->with([
                'detailsOrdered' => function ($que) use ($officeId, $vpOfficeId) {
                    $que->whereHas('offices', function ($query) use ($officeId, $vpOfficeId)  {
                        $query->where(function ($que) use ($officeId, $vpOfficeId) {
                            $que->where(function($q) use ($vpOfficeId) {
                                $q->where('office_id', '=', $vpOfficeId)
                                    ->whereNull('vp_office_id');
                            })->orWhere('office_id', '=', $officeId);
                        });
                    });
                }, 'detailsOrdered.offices', 'detailsOrdered.measures'
            ])->get();

        dd($getPIs);
    }
}
