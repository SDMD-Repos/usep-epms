<?php

namespace App\Http\Controllers\Form;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAapcr;
use App\Http\Requests\UpdateOpcrTemplate;
use App\Http\Traits\ConverterTrait;
use App\Http\Traits\FormTrait;
use App\Http\Traits\OfficeTrait;
use App\Opcr;
use App\OpcrTemplate;
use App\OpcrTemplateDetails;
use App\OpcrTemplateDetailsMeasures;
use App\VpOpcr;
use App\VpOpcrDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OcpcrController extends Controller
{
    use ConverterTrait, OfficeTrait, FormTrait;

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

        if($template) {
            foreach($template->detailParents as $detailParent) {
                $detailParent['type'] = 'pi';
                $detailParent['fromTemplate'] = 1;

                $sub = [];

                if(count($detailParent->subDetails)) {
                    foreach($detailParent->subDetails as $subDetail) {
                        $subDetail['type'] = 'sub';
                        $subDetail['fromTemplate'] = 1;

                        $sub[] = $this->getOpcrDetails($subDetail);
                    }
                }

                $data = $this->getOpcrDetails($detailParent, $sub);

                $dataSource[] = $data;
            }
        }

        # Get all indicators from VPs' OPCRs
        $vpOpcrs = VpOpcr::where([
            ['year', $year],
            ['is_active', 1],
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

        $vpIndicators = [];

        if($vpOpcrs) {
            foreach($vpOpcrs as $vpOpcr) {
                if($vpOpcr->detailsOrdered) {
                    foreach ($vpOpcr->detailsOrdered as $detail) {
                        $find = array_filter($vpIndicators, function($prop) use ($detail) {
                            return $prop->is_header === $detail->is_header && $prop->pi_name === $detail->pi_name &&
                                $prop->target === $detail->target && $prop->allocated_budget === $detail->allocated_budget &&
                                $prop->targets_basis === $detail->targets_basis && $prop->category_id === $detail->category_id &&
                                $prop->sub_category_id === $detail->sub_category_id && $prop->program_id === $detail->program_id;
                        });

                        if(!$find) {
                            $vpIndicators[] = $detail;
                        }
                    }
                }
            }

            $this->targetsBasisList = []; // Reset $this->targetsBasisList from ConverterTrait

            $parentIds = []; // To store parent PIs' id for tracking purposes

            if($vpIndicators) {
                foreach($vpIndicators as $vpIndicator) {

                    $categoryProgram = [];

                    $programId = $vpIndicator->program_id;

                    $extracted = $this->extractDetails($vpIndicator);

                    $subCategory = $extracted['subCategory'];

                    $offices = $this->splitPIOffices($vpIndicator, ['splitOffices' => 1, 'origin' => 'opcr']);

                    $this->getTargetsBasisList($vpIndicator->targets_basis);

                    $tempCategoryList = [];

                    foreach($vpIndicator->offices as $indicatorOffice) {

                        $indicatorOfficeId = (int)$indicatorOffice['office_id'];

                        if((($indicatorOfficeId === $vpOfficeId && $indicatorOffice['vp_office_id'] === null)
                            || ($indicatorOfficeId === (int)$officeId))) {
                            $categoryIndicator = !$indicatorOffice['category_id'] ?
                                (!$indicatorOffice['program_id'] ?
                                    ($indicatorOffice['other_program_id'] ? $indicatorOffice->otherProgram->category : null)
                                    : $indicatorOffice->program->category)
                                : $indicatorOffice->category;

                            $isCategoryExists = array_filter($tempCategoryList, function($x) use ($categoryIndicator){
                                return $x['id'] === $categoryIndicator;
                            });

                            $categoryProgram[] = [
                                'category' => $categoryIndicator,
                                'program' => $indicatorOffice['program_id'] ? : ($indicatorOffice['other_program_id'] ? : $programId)
                            ];

                            if(!$isCategoryExists) {
                                $tempCategoryList[] = $categoryIndicator;
                            }
                        }
                    }

                    foreach($tempCategoryList as $categoryList) {
                        $findCategory = array_filter($categoryProgram, function($x) use ($categoryList){
                            return $x['category'] === $categoryList->id;
                        });

                        if($findCategory) {
                            $programId = $findCategory[0]['program'];
                        }

                        $detail = [];

                        $detailIndex = $categoryList->id;

                        /*if($data->category_id !== $detailIndex){
                            $subCategory = null;
                            $programId = $categoryList->default_program_id;
                        }*/

                        $item = array(
                            'key' => $vpIndicator->id,
                            'id' => $vpIndicator->id,
                            'category' => $detailIndex,
                            'subCategory' => $subCategory,
                            'program' => $programId,
                            'name' => $vpIndicator->pi_name,
                            'isHeader' => false,
                            'target' => $vpIndicator->target,
                            'measures' => $extracted['measures'],
                            'budget' => $vpIndicator->allocated_budget,
                            'targetsBasis' => $vpIndicator->targets_basis,
                            'cascadingLevel' => $extracted['cascadingLevel'],
                            'implementing' => $offices['implementing'],
                            'supporting' => $offices['supporting'] ?? [],
                            'remarks' => $vpIndicator->other_remarks,
                            'deleted' => 0,
                            'isCascaded' => 1
                        );

                        if($vpIndicator->parent_id) {
                            $parent = VpOpcrDetail::where('id', $vpIndicator->parent_id)->first();

                            if($parent) {
                                $isExists = 0;

                                foreach($parentIds as $id) {
                                    if($id['id'] === $parent->id && $detailIndex === $id['index']) {
                                        $isExists = 1;
                                    }
                                }

                                if(!$isExists){
                                    if($parent->is_header) {

                                        $detail = array(
                                            'key' => $parent->id,
                                            'id' => $parent->id,
                                            'type' => 'pi',
                                            'category' => $detailIndex,
                                            'subCategory' => $subCategory,
                                            'program' => $programId,
                                            'name' => $parent->pi_name,
                                            'isHeader' => true,
                                            'target' => '',
                                            'measures' => [],
                                            'budget' => 0.00,
                                            'targetsBasis' => '',
                                            'cascadingLevel' => '',
                                            'implementing' => [],
                                            'supporting' => [],
                                            'remarks' => '',
                                            'children' => [],
                                            'deleted' => 0,
                                            'isCascaded' => 1
                                        );

                                        $item['type'] = 'sub';
                                        $item['fromTemplate'] = 0;

                                        $detail['children'][] = $item;

                                        $parentIds[] = array(
                                            'id' => $parent->id,
                                            'index' => $detailIndex
                                        );

                                    }else{

                                        $item['type'] = 'pi';
                                        $item['fromTemplate'] = 0;

                                        $detail = $item;
                                    }
                                }else{
                                    foreach($dataSource as $key =>  $source) {
                                        if($source['id'] === $parent->id && $detailIndex === $source['category']) {

                                            $item['type'] = 'sub';
                                            $item['fromTemplate'] = 0;

                                            $dataSource[$key]['children'][] = $item;
                                        }
                                    }
                                }
                            }else {

                            }
                        }else{

                            $item['type'] = 'pi';
                            $item['fromTemplate'] = 0;

                            $detail = $item;

                            $parentIds[] = array(
                                'id' => $vpIndicator->id,
                                'index' => $detailIndex
                            );
                        }

                        if(count($detail)) {
//                            dd($detail);
                            $dataSource[] = $detail; // Add new PI to array
                        }
                    }
                }
            }
        }

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
            'wasSaved' => $data->wasSaved,
        );

        if(count($children)){
            if(is_array($children)) {
                $details['children'] = $children;
            } else {
                $details['children'][] = $children;
            }
        }

        return $details;
    }

    public function getAllOpcr()
    {
        $list = Opcr::select("*", "id as key")->orderBy('created_at', 'ASC')->get();

        return response()->json([
            'list' => $list
        ], 200);
    }

}
