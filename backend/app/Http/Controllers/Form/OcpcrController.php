<?php

namespace App\Http\Controllers\Form;

use App\Http\Controllers\Controller;
use App\Http\Traits\OfficeTrait;
use App\VpOpcr;
use App\OpcrTemplate;
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

    public function checkSaved($year)
    {
        $hasSaved = OpcrTemplate::where([
            ['year', $year],
            ['is_active', 1]
        ])->first();

        return response()->json([
            'hasSaved' => $hasSaved !== null
        ], 200);
    }

    public function getAllOpcrTemplates()
    {
        $list = OpcrTemplate::select("*", "id as key")->orderBy('created_at', 'ASC')->get();

        return response()->json([
            'list' => $list
        ], 200);
    }

}
