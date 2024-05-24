<?php

namespace App\Http\Controllers\Form;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAapcr;
use App\Http\Requests\UpdateOpcrTemplate;
use App\Http\Traits\FormTrait;
use App\Http\Traits\OfficeTrait;
use App\Opcr;
use App\OpcrTemplate;
use App\OpcrTemplateDetails;
use App\OpcrTemplateDetailsMeasures;
use App\VpOpcr;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OcpcrController extends Controller
{
    use OfficeTrait, FormTrait;

    private $login_user;

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

    public function checkSaved($officeId, $year)
    {
        $hasSaved = Opcr::where([
            ['year', $year],
            ['office_id', $officeId],
            ['is_active', 1],
        ])->first();

        return response()->json([
            'hasSaved' => $hasSaved !== null
        ], 200);
    }

    public function getVpOpcrDetails($officeId, $year, $formId)
    {
        # Check if all VP's OPCR were all published
        $mainOffices = $this->getMainOfficesOnly(1, 0);

        $vpOpcrs = [];

        foreach ($mainOffices as $mainOffice) {
            $vpOpcrs[] = $mainOffice->value;
        }

        $hasUnpublished = VpOpcr::where('year', $year)->whereIn('office_id', $vpOpcrs)->whereNotNull('published_date')->where('is_active', 1)->get();

        if(count($hasUnpublished) !== count($vpOpcrs)) {
            return response()->json(['error' => "Unable to create this form at this time. Please wait for all VPs to publish their OPCR."], 200);
        }

        # Parent ID checker
        $vpOfficeId = $this->getOfficeParentId($officeId, $formId);

        if($formId === 'opcr' && $vpOfficeId === null) {
            return response()->json(['error' => "Department selected has no VP office assigned. Please contact the administrator"], 200);
        }

        # Get all indicators from template
        $template = OpcrTemplate::where([
            ['year', $year],
            ['is_active', 1]
        ])->whereNotNull('published_date')->with('detailParents.subDetails')->first();

        $dataSource = [];

        foreach($template->detailParents as $detailParent) {
            $sub = [];

            if(count($detailParent->subDetails)) {
                foreach($detailParent->subDetails as $subDetail) {
                    $sub[] = $this->getOpcrDetails($subDetail);
                }
            }

            $data = $this->getOpcrDetails($detailParent, $sub);
//            var_dump($data['name']);
            $dataSource[] = $data;
        }
//        dd($dataSource);

        # Get all indicators from VPs' OPCRs
        /*$getPIs = VpOpcr::where([
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
            ])->get();*/

//        dd($getPIs);
        return response()->json([
            'dataSource' => $dataSource,
            'targetsBasisList' => $this->targetsBasisList,
        ], 200);
    }

    public function getOpcrDetails($data, $children=[])
    {
        $offices = $data->offices ? $this->splitPIOffices($data->offices, ['splitOffices' => 1, 'origin' => 'vpopcr-view']) : [];

        $this->getTargetsBasisList($data->targets_basis);

        $extracted = $this->extractDetails($data);

        $details = array(
            'key' => $data->id,
            'id' => $data->id,
            'type' => $data->type,
            'category' => $data->category_id,
            'subCategory' => $extracted['subCategory'],
            'program' => $data->program_id,
            'name' => $data->pi_name,
            'isHeader' => (bool)$data->is_header,
            'target' => $data->target,
            'measures' => $extracted['measures'],
            'budget' => $data->allocated_budget,
            'targetsBasis' => $data->targets_basis,
            'implementing' => $offices['implementing'] ?? [],
            'supporting' => $offices['supporting'] ?? [],
            'remarks' => $data->remarks,
            'deleted' => 0,
            'isCascaded' => $data->isCascaded,
            'fromTemplate' => $data->fromTemplate,
            'wasSaved' => $data->wasSaved
        );

        if(count($children)){
            $details['children'][] = $children;
        }

        return $details;
    }

}
