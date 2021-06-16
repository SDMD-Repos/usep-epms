<?php

namespace App\Http\Controllers;

use App\Aapcr;
use App\AapcrDetailOffice;
use App\Http\Classes\Jasperreport;
use App\Http\Traits\ConverterTrait;
use App\Program;
use App\Signatory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class AppController extends Controller
{
    use ConverterTrait;

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

    public function viewPdf($id, $documentName)
    {
        $aapcr = Aapcr::find($id);

        $signatories = Signatory::where([
            ['year', $aapcr->year],
            ['form_id', 'aapcr']
        ])->get();

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

        $preparedDate = ($aapcr->finalized_date ? date("d F Y", strtotime($aapcr->finalized_date)) : '');

        if(isset($signatoryList['prepared_by'])) {
            $preparedBy = $signatoryList['prepared_by'][0];

            $preparedByName = $preparedBy['name'];
            $preparedByPosition = $preparedBy['position'] . ", " . $preparedBy['office_name'];
        } else {
            $preparedByName = "";
            $preparedByPosition = "";
        }

        $reviewedDate = ($aapcr->reviewed_date ? date("d F Y", strtotime($aapcr->reviewed_date)) : '');

        if(isset($signatoryList['reviewed_by'])) {
            $reviewedBy = $signatoryList['reviewed_by'][0];

            $reviewedByName = $reviewedBy['name'];
            $reviewedByPosition = $reviewedBy['position'] . ", " . $reviewedBy['office_name'];
        } else {
            $reviewedByName = "";
            $reviewedByPosition = "";
        }

        $approvedDate = ($aapcr->approved_date ? date("d F Y", strtotime($aapcr->approved_date)) : '');

        if (isset($signatoryList['approved_by'])) {
            $approvedBy = $signatoryList['approved_by'][0];

            $approvedByName = $approvedBy['name'];
            $approvedByPosition = $approvedBy['position'];
        } else {
            $approvedByName = "";
            $approvedByPosition = "";
        }

        $programs = Program::all();

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
            ->orderBy('program_id', 'ASC')->orderBy('sub_category_id', 'ASC')->get();

        $PICount = 0;

        $aapcrBudgets = $aapcr->budgets;

        $tempProgramId = 0;

        $totalBudget = 0;

        foreach($details as $key => $detail) {
            $budget = 0;

            $function = $this->integerToRomanNumeral($detail->category->order) . ". " . mb_strtoupper($detail->category->name);

            $program = ($detail->category_id === 'core_functions' ? strtoupper($detail->program->name) : $detail->program->name);

            $subCategory = ($detail->sub_category_id ? $detail->subCategory->name : NULL);

            $measures = $this->fetchMeasuresPdf($detail->measures);

            $getOffices = $this->getOfficesPdf($detail->id);

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

                    $getSubOffices = $this->getOfficesPdf($subPi->id);

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

        $params = [
            'usepLogo' => public_path()."/logos/USeP_Logo.png",
            'notFinal' => !$aapcr->published_date || !$aapcr->is_active ? public_path()."/logos/notfinal.png" : "",
            'totalBudget' => number_format($totalBudget, 2),
            'year' => $aapcr->year,
            'preparedBy' => strtoupper($preparedByName),
            'preparedByPosition' => $preparedByPosition,
            'preparedDate' => $preparedDate,
            'reviewedBy' => $reviewedByName,
            'reviewedByPosition' => $reviewedByPosition,
            'reviewedDate' => $reviewedDate,
            'approvedBy' => strtoupper($approvedByName),
            'approvedDate' => $approvedDate,
            'approvingPosition' => $approvedByPosition,
            'programsDataSet' => $programsDataSet,
            'public_path' => public_path()
        ];

        $jasperReport = new Jasperreport();
        $jasperReport->showReport('aapcr', $params, $data, 'PDF', $documentName);

        $pdf = public_path('forms/'.$documentName.".pdf");

        return response()->download($pdf);
    }

    public function fetchMeasuresPdf($details)
    {
        $measures = array();

        foreach($details as $PIMeasure) {
            array_push($measures, $PIMeasure->name);
        }

        return $measures;
    }

    public function getOfficesPdf($detailId)
    {
        $getOffices = AapcrDetailOffice::select('office_name', 'office_type_id')
            ->where('detail_id', $detailId)
            ->orderBy('office_type_id', 'asc')
            ->get();

        $allOffices = [
            'implementing' => array(),
            'supporting' => array()
        ];

        foreach($getOffices as $getOffice) {
            if($getOffice['office_type_id'] === 'implementing') {
                array_push($allOffices['implementing'], $getOffice['office_name']);
            }else{
                array_push($allOffices['supporting'], $getOffice['office_name']);
            }
        }

        return $allOffices;
    }
}
