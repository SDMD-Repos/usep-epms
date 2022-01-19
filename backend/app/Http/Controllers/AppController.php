<?php

namespace App\Http\Controllers;

use App\Aapcr;
use App\AapcrDetail;
use App\AapcrDetailOffice;
use App\Http\Classes\Jasperreport;
use App\Http\Traits\ConverterTrait;
use App\Program;
use App\Signatory;
use App\VpOpcr;
use App\VpOpcrDetailOffice;
use Illuminate\Support\Facades\Auth;

class AppController extends Controller
{
    use ConverterTrait;

    private $login_user;

    private $programDataSet= [];

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

    // AAPCR
    public function viewAapcrPdf($id)
    {
        $aapcr = Aapcr::find($id);

        $documentName = $aapcr->document_name;

        $officeModel = new AapcrDetailOffice();

        $signatory = $this->getSignatories($aapcr, "aapcr");

        $programs = Program::orderBy('category_id', 'asc')->get();

        $programsDataSet = array();

        foreach ($programs as $programKey => $program) {
            $categoryName = $this->integerToRomanNumeral($program->category->order) . ". " . mb_strtoupper($program->category->name);

            $programsDataSet[$programKey] = array(
                'categoryName' => $categoryName,
                'categoryPercentage' => $program->category->percentage,
                'programName' => $program->name,
                'programPercentage' => $program->percentage
            );
        }
        $data[0] = array();

        $details = $aapcr->details()->where('parent_id', NULL)
            ->orderBy('category_id', 'ASC')
            ->orderBy('program_id', 'ASC')
            ->orderBy('sub_category_id', 'ASC')->get();

        $PICount = 0;

        $aapcrBudgets = $aapcr->budgets;

        $tempProgramId = 0;

        $totalBudget = 0;

        foreach($details as $detail) {
            $budget = 0;

            $function = $this->integerToRomanNumeral($detail->category->order) . ". " . mb_strtoupper($detail->category->name);

            $program = ($detail->category_id === 'core_functions' ? strtoupper($detail->program->name) : $detail->program->name);

            $subCategory = ($detail->sub_category_id ? $detail->subCategory->name : NULL);

            $measures = $this->fetchMeasuresPdf($detail->measures);

            $getOffices = $this->getOfficesPdf($officeModel, $detail->id);

            if(!$tempProgramId || $tempProgramId !== $detail->program_id){
                $tempProgramId = $detail->program_id;

                foreach ($aapcrBudgets as $aapcrBudget) {
                    if($aapcrBudget->program_id === $detail->program_id){
                        $budget = $aapcrBudget->budget;
                        $totalBudget += $aapcrBudget->budget;
                        break;
                    }
                }
            }

            $data[$PICount] = array(
                'category_id' => $detail->category_id,
                'function' => $function,
                'program' => $program,
                'subCategory' => $subCategory,
                'pi_name' => $detail->pi_name,
                'target' => $detail->target,
                'measures' => implode(", ", $measures),
                'allocatedBudget' => $detail->allocated_budget ? number_format($detail->allocated_budget) : '',
                'targetsBasis' => $detail->targets_basis,
                'implementing' => implode(", ", $getOffices['implementing']),
                'supporting' => implode(", ", $getOffices['supporting']),
                'programBudget' => $budget,
                'otherRemarks' => $detail->other_remarks,
                'subPICount' => 0
            );

            if($detail->sub_category_id !== NULL && $detail->subCategory->parent_id !== NULL) {
                $this->parentSubCategories = [];

                $this->getParentSubCategories($detail->subCategory->parent_id);

                $reversedSubCategories = array_reverse($this->parentSubCategories);

                foreach($reversedSubCategories as $dataKey => $subParent) {
                    $subCategoryKey = "subCategoryParent_".($dataKey+1);

                    $data[$PICount][$subCategoryKey] = $subParent;
                }
            }

            $PICount++;

            $subPis = $aapcr->details()->where('parent_id', $detail->id)->get();

            if(count($subPis)){
                foreach($subPis as $subKey => $subPi) {

                    $subMeasures = $this->fetchMeasuresPdf($subPi->measures);

                    $getSubOffices = $this->getOfficesPdf($officeModel, $subPi->id);

                    $data[$PICount] = array(
                        'category_id' => $detail->category_id,
                        'function' => $function,
                        'program' => $program,
                        'subCategory' => $subCategory,
                        'pi_name' => $subPi->pi_name,
                        'target' => $subPi->target,
                        'measures' => implode(", ", $subMeasures),
                        'allocatedBudget' => $subPi->allocated_budget ? number_format($subPi->allocated_budget) : '',
                        'targetsBasis' => $subPi->targets_basis,
                        'implementing' => implode(', ', $getSubOffices['implementing']),
                        'supporting' => implode(', ', $getSubOffices['supporting']),
                        'otherRemarks' => $subPi->other_remarks,
                        'subPICount' => $subKey+1
                    );

                    if($detail->sub_category_id !== NULL && $detail->subCategory->parent_id !== NULL) {
                        foreach($reversedSubCategories as $key => $subParent) {
                            $subCategoryKey = "subCategoryParent_".($key+1);

                            $data[$PICount][$subCategoryKey] = $subParent;
                        }
                    }

                    $PICount++;
                }
            }
        }

        $publicPath = public_path();

        $params = [
            'usepLogo' => $publicPath."/logos/USeP_Logo.png",
            'notFinal' => !$aapcr->published_date || !$aapcr->is_active ? $publicPath."/logos/notfinal.png" : "",
            'totalBudget' => number_format($totalBudget, 2),
            'year' => $aapcr->year,
            'preparedBy' => strtoupper($signatory['preparedBy']),
            'preparedByPosition' => $signatory['preparedByPosition'],
            'preparedDate' => $signatory['preparedDate'],
            'reviewedBy' => $signatory['reviewedBy'],
            'reviewedByPosition' => $signatory['reviewedByPosition'],
            'reviewedDate' => $signatory['reviewedDate'],
            'approvedBy' => strtoupper($signatory['approvedBy']),
            'approvedDate' => $signatory['approvedDate'],
            'approvingPosition' => $signatory['approvedByPosition'],
            'programsDataSet' => $programsDataSet,
            'public_path' => $publicPath
        ];

        $jasperReport = new Jasperreport();
        $jasperReport->showReport('aapcr', $params, $data, 'PDF', $documentName);

        $pdf = public_path('forms/'.$documentName.".pdf");

        return response()->download($pdf);
    }

    // VP's OPCR

    public function viewVpOpcrPdf($id)
    {
        $vpopcr = VpOpcr::find($id);

        $officeId = $vpopcr->office_id;

        $signatory = $this->getSignatories($vpopcr, 'vpopcr');

        $documentName = str_replace(" ","_", $vpopcr->office_name);

        $details = $vpopcr->details()->where('parent_id', NULL)
            ->orderBy('category_id', 'ASC')
            ->orderByRaw('-program_id DESC')
            ->orderByRaw('!ISNULL(sub_category_id), sub_category_id ASC')
            ->orderByRaw('-`aapcr_detail_id` DESC')
            ->orderBy('created_at', 'ASC')->get();

        $dataSource = [];

        $parentIds = []; // To store parent PIs' id for tracking purposes

        foreach($details as $detail) {
            $stored = 0;

            $isParent = 0;

            $parentDetails = null;

            $data = $this->getVpOpcrPdfDetails($detail, []);

            if($detail->aapcr_detail_id) {
                $aapcrDetail = $detail->aapcrDetail;

                if($aapcrDetail->parent_id && $detail->from_aapcr) {
                    if(!$detail->aapcrDetail->parent->is_header) {
                        $parentDetail = AapcrDetail::whereHas('offices', function($query) use ($officeId) {
                            $query->where(function($q) use ($officeId) {
                                $q->where('vp_office_id', '=', $officeId)
                                    ->orWhere('office_id', '=', $officeId);
                            });
                        })->with(['measures', 'offices'])->where('id', $aapcrDetail->parent_id)->first();

                        if($parentDetail) {
                            $isParent = 1;

                            $parentDetail->isParent = 1;

                            $parentDetail->category_id = $detail->category_id;

                            $parentDetail->sub_category_id = $detail->sub_category_id;

                            $parentDetail->program_id = $detail->program_id;

                            $parentDetails = $parentDetail;

                        }else{
                            $stored = 1;
                        }
                    }else {
                        $isParent = 1;

                        $detail->aapcrDetail->parent->isParent = 1;

                        $parentDetails = $detail->aapcrDetail->parent;
                    }
                } else {
                    if(!$detail->from_aapcr) {
                        $isParent = 1;

                        $aapcrDetail->isParent = 1;

                        $parentDetails = $aapcrDetail;
                    }else{

                        $stored = 1;
                    }
                }
            } else {
                $stored = 1;
            }

            if($isParent) {
                $isExists = 0;

                foreach($parentIds as $id) {
                    if($id['id'] === $parentDetails->id && $detail->category_id === $id['index']) {
                        $isExists = 1;
                    }
                }

                if(!$isExists) {
                    if($parentDetails->category_id !== $detail->category_id){
                        $parentDetails->category_id = $detail->category_id;

                        $parentDetails->sub_category = null;
                    }else{
                        $parentDetails->sub_category = $detail->sub_category_id;
                    }

                    $dataSource[] = $this->getVpOpcrPdfDetails($parentDetails, $data);

                    array_push($parentIds, array(
                        'id' => $parentDetails->id,
                        'index' => $detail->category_id
                    ));
                } else {
                    foreach($dataSource as $key => $source) {
                        if($source['id'] === $parentDetails->id && $detail->category_id == $source['categoryId']) {

                            $dataSource[$key]['children'][] = $data;
                        }
                    }
                }
            } elseif($stored) {
                if(count($detail->subDetails)) {
                    $subs = [];

                    foreach($detail->subDetails as $subDetail) {

                        $subs[] = $this->getVpOpcrPdfDetails($subDetail, []);
                    }

                    $data['children'] = $subs;
                }

                $dataSource[] = $data;

                array_push($parentIds, array(
                    'id' => $detail->id,
                    'index' => $detail->category_id
                ));
            }
        }

        $publicPath = public_path();

        $params = array(
            'usepLogo' => $publicPath."/logos/USeP_Logo.png",
            'public_path' => $publicPath,
            'notFinalImage' => !$vpopcr->published_date || !$vpopcr->is_active ? $publicPath."/logos/notfinal.png" : "",
            'year' => $vpopcr->year,
            'vpOfficeName' => $vpopcr->office_name,
            'preparedBy' => strtoupper($signatory['preparedBy']),
            'preparedByPosition' => $signatory['preparedByPosition'],
            'preparedDate' => $signatory['preparedDate'],
            'reviewedBy' => $signatory['reviewedBy'],
            'reviewedByPosition' => $signatory['reviewedByPosition'],
            'reviewedDate' => $signatory['reviewedDate'],
            'approvedBy' => strtoupper($signatory['approvedBy']),
            'approvedDate' => $signatory['approvedDate'],
            'approvingPosition' => $signatory['approvedByPosition'],
            'assessedBy' => 'NAME:',
            'assessedByPosition' => 'PMT-PMG Member/Secretariat',
            'programsDataSet' => $this->programDataSet
        );

        $jasperReport = new Jasperreport();
        $jasperReport->showReport('vpopcr', $params, $dataSource, 'PDF', $documentName);

        $pdf = public_path('forms/'.$documentName.".pdf");

        return response()->download($pdf);
    }

    public function getVpOpcrPdfDetails($detail, $children)
    {
        $officeModel = new VpOpcrDetailOffice();

        $function = $this->integerToRomanNumeral($detail->category->order) . ". " . mb_strtoupper($detail->category->name);

        $program = NULL;

        if($detail->category_id === 'core_functions' && $detail->sub_category_id !== NULL) {
            $program = strtoupper($detail->program->name);
        }else if($detail->category_id === 'support_functions'){
            $program = $detail->program->name;
        }

        $subCategory = ($detail->sub_category_id ? $detail->subCategory->name : NULL);

        $measures = '' ;

        $getOffices = [];

        if(!$detail->is_header) {
            $measures = $this->fetchMeasuresPdf($detail->measures);

            if(isset($detail->isParent) && $detail->isParent) {
                $officeModel = new AapcrDetailOffice();
            }

            $getOffices = $this->getOfficesPdf($officeModel, $detail->id);
        }

        $data = array(
            'id' => $detail->id,
            'categoryId' => $detail->category_id,
            'function' => $function,
            'program' => $program,
            'subCategory' => $subCategory,
            'piName' => $detail->pi_name,
            'target' => $detail->target,
            'measures' => $measures ? implode(", ", $measures) : '',
            'allocatedBudget' => $detail->allocated_budget ? number_format($detail->allocated_budget) : '',
            'targetsBasis' => $detail->targets_basis,
            'implementing' => isset($getOffices['implementing']) ? implode(", ", $getOffices['implementing']) : '',
            'supporting' => isset($getOffices['supporting']) ? implode(", ", $getOffices['supporting']) : '',
        );

        if($detail->sub_category_id !== NULL && $detail->subCategory->parent_id !== NULL) {
            $this->parentSubCategories = [];

            $this->getParentSubCategories($detail->subCategory->parent_id);

            $reversedSubCategories = array_reverse($this->parentSubCategories);

            foreach($reversedSubCategories as $dataKey => $subParent) {
                $subCategoryKey = "subCategoryParent_".($dataKey+1);

                $data[$subCategoryKey] = $subParent;
            }
        }

        if(count($children)){
            $data['children'][] = $children;
        }

        if($detail->category_id === 'support_functions' || ($detail->category_id === 'core_functions' && $detail->sub_category_id !== NULL)){

            $ifSaved = $this->array_any(function($x, $compare){
                return $x['programName'] === ucwords($compare['progName']);
            }, $this->programDataSet, ['progName' => strtolower($program)]);

            if(!$ifSaved) {

                $categoryName = $this->integerToRomanNumeral($detail->category->order) . ". " . mb_strtoupper($detail->category->name);

                $this->programDataSet[] = array(
                    'categoryName' => $categoryName,
                    'categoryPercentage' => $detail->category->percentage,
                    'programName' => ucwords($detail->program->name),
                    'programPercentage' => $detail->program->percentage
                );
            }
        }

        return $data;
    }

    public function getSignatories($model, $form)
    {
        if($form === 'vpopcr') {
            $signatories = Signatory::where([
                ['year', $model->year],
                ['form_id', $form],
                ['office_form_id', $model->office_id]
            ])->get();
        } else {
            $signatories = Signatory::where([
                ['year', $model->year],
                ['form_id', $form]
            ])->get();
        }

        $signatoryList = [];

        foreach($signatories as $signatory) {
            $dataSignatories = [
                'name' => strtoupper($signatory['personnel_name']),
                'position' => $signatory['position'],
                'office_name' => $signatory['office_name'],
            ];

            if(!array_key_exists($signatory->type_id, $signatoryList)) {
                $signatoryList[$signatory->type_id] = [];
            }

            array_push($signatoryList[$signatory->type_id], $dataSignatories);
        }

        $preparedDate = ($model->finalized_date ? date("d F Y", strtotime($model->finalized_date)) : '');

        if(isset($signatoryList['prepared_by'])) {
            $preparedBy = $signatoryList['prepared_by'][0];

            $preparedByName = $preparedBy['name'];

            if($form === 'vpopcr') {
                $preparedByPosition = str_replace('Office of the ', '', $preparedBy['office_name']);
            } else {
                $preparedByPosition = $preparedBy['position'] . ", " . $preparedBy['office_name'];
            }
        } else {
            $preparedByName = "";
            $preparedByPosition = "";
        }

        $reviewedDate = ($model->reviewed_date ? date("d F Y", strtotime($model->reviewed_date)) : '');

        if(isset($signatoryList['reviewed_by'])) {
            $reviewedBy = $signatoryList['reviewed_by'][0];

            $reviewedByName = $reviewedBy['name'];

            if($form === 'vpopcr') {
                $reviewedByPosition = $reviewedBy['office_name'] . " " . $reviewedBy['position'];
            } else {
                $reviewedByPosition = $reviewedBy['position'] . ", " . $reviewedBy['office_name'];
            }
        } else {
            $reviewedByName = "";
            $reviewedByPosition = "";
        }

        $approvedDate = ($model->approved_date ? date("d F Y", strtotime($model->approved_date)) : '');

        if (isset($signatoryList['approved_by'])) {
            $approvedBy = $signatoryList['approved_by'][0];

            $approvedByName = $approvedBy['name'];
            $approvedByPosition = $approvedBy['position'];
        } else {
            $approvedByName = "";
            $approvedByPosition = "";
        }

        return [
            'preparedDate' => $preparedDate,
            'preparedBy' => $preparedByName,
            'preparedByPosition' => $preparedByPosition,
            'reviewedDate' => $reviewedDate,
            'reviewedBy' => $reviewedByName,
            'reviewedByPosition' => $reviewedByPosition,
            'approvedDate' => $approvedDate,
            'approvedBy' => $approvedByName,
            'approvedByPosition' => $approvedByPosition,
        ];
    }

    public function fetchMeasuresPdf($details)
    {
        $measures = array();

        foreach($details as $PIMeasure) {
            array_push($measures, $PIMeasure->name);
        }

        return $measures;
    }

    public function getOfficesPdf($model, $detailId)
    {
        $getOffices = $model::select('office_name', 'office_type_id', 'is_group', 'group_id')
            ->where('detail_id', $detailId)
            ->orderBy('office_type_id', 'asc')
            ->get();

        $allOffices = [
            'implementing' => array(),
            'supporting' => array()
        ];

        foreach($getOffices as $getOffice) {
            $officeName = $getOffice['office_name'];

            if ($getOffice['is_group']) {
                $officeName = $getOffice->group->name;
            }

            if($getOffice['office_type_id'] === 'implementing') {
                array_push($allOffices['implementing'], $officeName);
            }else{
                array_push($allOffices['supporting'], $officeName);
            }
        }

        return $allOffices;
    }
}
