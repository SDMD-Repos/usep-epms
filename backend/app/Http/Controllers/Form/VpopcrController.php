<?php

namespace App\Http\Controllers\Form;

use App\Aapcr;
use App\AapcrDetail;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVpopcr;
use App\Http\Traits\ConverterTrait;
use App\VpOpcr;
use App\VpOpcrDetail;
use App\VpOpcrDetailOffice;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VpopcrController extends Controller
{
    use ConverterTrait;

    private $login_user;

    private $STO = 5;

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

    public function checkSaved($officeId, $year)
    {
        $hasSaved = VpOpcr::where([
            ['office_id', (int)$officeId],
            ['year', $year],
            ['is_active', 1]
        ])->first();

        return response()->json([
            'hasSaved' => $hasSaved !== null
        ], 200);
    }

    public function getAapcrDetails($vpId, $year)
    {
        $query = Aapcr::where([
            ['year', $year],
            ['is_active', 1]
        ])->whereNotNull('published_date')->with(['detailsOrdered' => function ($que) use ($vpId) {
            $que->whereHas('offices', function ($query) use ($vpId) {
                $query->where(function($q) use ($vpId) {
                    $q->where('vp_office_id', '=', $vpId)
                        ->orWhere('office_id', '=', $vpId);
                });
            });
        }, 'detailsOrdered.offices', 'detailsOrdered.measures'])->first();

        $dataSource = [];

        $this->targetsBasisList = []; // Reset $this->targetsBasisList from ConverterTrait

        $parentIds = []; // To store parent PIs' id for tracking purposes

        if ($query) {
            # Loop through PI details
            foreach($query->detailsOrdered as $data) {
                $detail = [];

                $programId = $data->program_id;

                $extracted = $this->extractDetails($data);

                $subCategory = $extracted['subCategory'];

                $offices = $this->splitPIOffices($data->offices, 1);

                $this->getTargetsBasisList($data->targets_basis);

                # Check whether PI will be cascaded in core or support functions
                # Set default sub category id and name based on the new category id
                $hasCore = $this->array_any(function ($x, $arr) {
                    return $x->cascade_to === 'core_functions' && ($x->vp_office_id === $arr['vpId'] || $x->office_id === $arr['vpId']);
                }, $data->offices, ['vpId' => $vpId]);

                if($hasCore){
                    $detailIndex = 'core_functions';

                    if($data->category_id === 'support_functions'){
                        $programId = null;
                    }
                }else{
                    $detailIndex = 'support_functions';

                    if($data->category_id === 'core_functions'){
                        $programId = $this->STO;
                    }
                }

                if($data->category_id !== $detailIndex){
                    $subCategory = null;
                }

                $this->getTargetsBasisList($data->targets_basis);

                $item = array(
                    'key' => $data->id,
                    'id' => $data->id,
                    'category' => $detailIndex,
                    'subCategory' => $subCategory,
                    'program' => $programId,
                    'name' => $data->pi_name,
                    'isHeader' => false,
                    'target' => $data->target,
                    'measures' => $extracted['measures'],
                    'budget' => $data->allocated_budget,
                    'targetsBasis' => $data->targets_basis,
                    'implementing' => $offices['implementing'],
                    'supporting' => $offices['supporting'] ?? [],
                    'remarks' => $data->other_remarks,
                    'deleted' => 0,
                    'isCascaded' => 1
                );

                if($data->parent_id) {
                    $parent = AapcrDetail::where('id', $data->parent_id)->first();

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
                                    'implementing' => [],
                                    'supporting' => [],
                                    'remarks' => '',
                                    'children' => [],
                                    'deleted' => 0,
                                    'isCascaded' => 1
                                );

                                $item['type'] = 'sub';

                                $detail['children'][] = $item;

                                array_push($parentIds, array(
                                    'id' => $parent->id,
                                    'index' => $detailIndex
                                ));
                            }else{
//                                $sub['children'] = [];
                                $item['type'] = 'pi';

                                $detail = $item;
                            }
                        }else{
                            foreach($dataSource as $key => $source) {
                                if($source['id'] === $parent->id && $detailIndex === $source['category']) {
                                    $item['type'] = 'sub';

                                    $dataSource[$key]['children'][] = $item;
                                }
                            }
                        }
                    }
                } else{
                    $item['type'] = 'pi';

                    $detail = $item;

                    array_push($parentIds, array(
                        'id' => $data->id,
                        'index' => $detailIndex
                    ));
                }

                if(count($detail)) {
                    $dataSource[] = $detail; // Add new PI to array
                }
            } # end loop of PI details
        }

        return response()->json([
            'dataSource' => $dataSource,
            'aapcrId' => $query->id ?? null,
            'targetsBasisList' => $this->targetsBasisList
        ], 200);
    }

    public function getAllVpOpcrs()
    {
        $list = VpOpcr::select("*", "id as key")->orderBy('created_at', 'ASC')->get();

        return response()->json([
            'list' => $list
        ], 200);
    }

    public function save(StoreVpopcr $request)
    {
        DB::beginTransaction();

        try {
            $validated = $request->validated();

            $dataSource = $validated['dataSource'];
            $year = $validated['fiscalYear'];
            $isFinalized = $validated['isFinalized'];
            $officeId = $validated['vpOffice']['key'];
            $officeName = $validated['vpOffice']['label'];
            $aapcrId = $validated['aapcrId'];

            $finalizedHistory = ($isFinalized ? 'and finalized ' : '');

            $vpopcr = new VpOpcr();

            $vpopcr->year = $year;
            $vpopcr->office_id = $officeId;
            $vpopcr->office_name = $officeName;
            $vpopcr->aapcr_id = $aapcrId;
            $vpopcr->finalized_date = ($isFinalized ? Carbon::now() : null);
            $vpopcr->create_id = $this->login_user->pmaps_id;
            $vpopcr->history = "Created ". $finalizedHistory . Carbon::now() . " by " . $this->login_user->fullName . "\n";

            $savedHeaders = [];

            if($vpopcr->save()) {
                foreach($dataSource as $source) {
                    $isNew = strpos($source['id'], 'new') !== false;

                    if(!$isNew){
                        $aapcrDetailId = $source['id'];

                        $getParentId = null;
                    }else{
                        $aapcrDetailId = null;
                    }

                    $storeHeader = 0;

                    if($source['isHeader'] && !in_array($source['key'], $savedHeaders)) {
                        $storeHeader = 1;
                        array_push($savedHeaders, $source['key']);
                    }

                    $saveDetail = (!$isNew && !isset($source['children']))
                        || ($isNew)
                        || $storeHeader;

                    if($saveDetail) {
                        $source['aapcr_detail_id'] = $aapcrDetailId;

                        $source['from_aapcr'] = !$isNew;

                        $source['parent_id'] = null;

                        $newDetail = $isNew || $storeHeader;

                        $getParentId = $this->saveVpOpcrDetails($vpopcr->id, $source, $newDetail);
                    }

                    if(isset($source['children']) && count($source['children'])) {
                        $parentAapcrDetailId = $aapcrDetailId;

                        foreach($source['children'] as $child) {
                            $newSub = strpos($child['id'], 'new') !== false;

                            $fromAapcr = !$newSub;

                            $aapcrDetailId = !$newSub ? $child['id'] : $parentAapcrDetailId;

                            $child['aapcr_detail_id'] = $aapcrDetailId;

                            $child['parent_id'] = $getParentId;

                            $child['from_aapcr'] = $fromAapcr;

                            $this->saveVpOpcrDetails($vpopcr->id, $child);
                        }
                    }
                }
            } else{
                DB::rollBack();
            }

            DB::commit();

            return response()->json("VP's OPCR was saved successfully", 200);
        } catch(\Exception $e){
            if (is_numeric($e->getCode()) && $e->getCode() && $e->getCode() < 511) {
                $status = $e->getCode();
            } else {
                $status = 400;
            }

            return response()->json($e->getMessage(), $status);
        }
    }

    public function saveVpOpcrDetails($id, $data, $isNew=0)
    {
        $detail = new VpOpcrDetail();

        $detail->vp_opcr_id = $id;
        $detail->aapcr_detail_id = $data['aapcr_detail_id'];
        $detail->is_header = $data['isHeader'];
        $detail->pi_name = $data['name'];
        $detail->target = $data['target'];
        $detail->allocated_budget = $data['budget'];
        $detail->targets_basis = $data['targetsBasis'];
        $detail->category_id = $data['category'];
        $detail->sub_category_id = $data['subCategory'] ? $data['subCategory']['value'] : $data['subCategory'];
        $detail->program_id = $data['program'];
        $detail->remarks = $data['remarks'];
        $detail->parent_id = $data['parent_id'];
        $detail->from_aapcr = $data['from_aapcr'];
        $detail->create_id = $this->login_user->pmaps_id;
        $detail->history = "Created " . Carbon::now() . " by " . $this->login_user->fullName . "\n";

        if($detail->save()){
            $this->saveMeasures($detail, $data['measures']);

            $this->saveOffices($detail->id, $data['implementing'], 'implementing');

            $this->saveOffices($detail->id, $data['supporting'], 'supporting');
        }

        if($isNew){
            return $detail->id;
        }
    }

    public function saveMeasures($detail, $measures)
    {
        foreach ($measures as $measure) {
            $detail->measures()->attach($measure['key'], [
                'create_id' => $this->login_user->pmaps_id,
                'history' => "Created " . Carbon::now() . " by " . $this->login_user->fullName . "\n"
            ]);
        }
    }

    public function saveOffices($detailId, $offices, $fieldName)
    {
        foreach($offices as $office){

            $newOffice = new VpOpcrDetailOffice();

            if(!array_key_exists('children', $office)){
                $newOffice->vp_office_id = $office['pId'];

                $office_name = $office['acronym'];
            }else{
                $office_name = $office['label'];
            }

            $newOffice->detail_id = $detailId;
            $newOffice->office_type_id = $fieldName;
            $newOffice->cascade_to = $office['cascadeTo'];
            $newOffice->office_id = $office['value'];
            $newOffice->office_name = $office_name;
            $newOffice->create_id = $this->login_user->pmaps_id;
            $newOffice->history = "Created " . Carbon::now() . " by " . $this->login_user->fullName . "\n";

            $newOffice->save();
        }
    }

    public function publish(Request $request)
    {
        $id = $request->id;
        $year = $request->year;
        $officeId = $request->officeId;

        $hasPublished = VpOpcr::where([
            ['year', $year],
            ['office_id', $officeId],
            ['is_active', 1]
        ])->whereNotNull('published_date')->first();

        if(!$hasPublished) {
            $vpopcr = VpOpcr::find($id);

            $vpopcr->published_date = Carbon::now();
            $vpopcr->updated_at = Carbon::now();
            $vpopcr->modify_id = $this->login_user->pmaps_id;
            $vpopcr->history = $vpopcr->history . "Published " . Carbon::now() . " by " . $this->login_user->fullName . "\n";

            $vpopcr->save();

            return response()->json('OPCR was published successfully', 200);
        }else{
            return response()->json('Cannot publish two or more OPCRs for '.$hasPublished->office_name.' in a year', 400);
        }
    }

    public function deactivate(Request $request)
    {
        $id = $request->id;

        $now = Carbon::now();

        $vpopcr = VpOpcr::find($id);

        $vpopcr->is_active = 0;
        $vpopcr->end_effectivity = $now;
        $vpopcr->updated_at = $now;
        $vpopcr->modify_id = $this->login_user->pmaps_id;
        $vpopcr->history = $vpopcr->history . "Deactivated " . $now . " by " . $this->login_user->fullName . "\n";

        $vpopcr->save();

        return response()->json("Successfully deactivated", 200);
    }

    public function view($id)
    {
        $vpOpcr = VpOpcr::with([
            'detailParents',
            'detailParents.aapcrDetail',
            'detailParents.measures',
            'detailParents.offices',
            'detailParents.subDetails'
        ])->where('id', $id)->first();

        $officeId = $vpOpcr->office_id;

        $dataSource = [];

        $this->targetsBasisList = []; // Reset $this->targetsBasisList from ConverterTrait

        $parentIds = []; // To store parent PIs' id for tracking purposes

        foreach($vpOpcr->detailParents as $detail) {
            $stored = 0;

            $isParent = 0;

            $parentDetails = null;

            $detail->isCascaded = $detail->from_aapcr;

            $detail->wasSaved = 1;

            $extracted = $this->extractDetails($detail);

            $subCategory = $extracted['subCategory'];

            $data = $this->getVpOpcrDetails($detail, []);

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

                            $parentDetails = $parentDetail;
                        }else{
                            $stored = 1;
                        }
                    }else {
                        $isParent = 1;

                        $parentDetails = $detail->aapcrDetail->parent;
                    }
                } else {
                    if(!$detail->from_aapcr) {
                        $isParent = 1;

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
                    $parentDetails->isCascaded = 1;

                    $parentDetails->wasSaved = 0;

                    if($parentDetails->category_id !== $detail->category_id){
                        $parentDetails->sub_category = null;
                    }else{
                        $parentDetails->sub_category = $subCategory;
                    }

                    $dataSource[] = $this->getVpOpcrDetails($parentDetails, $data);

                    array_push($parentIds, array(
                        'id' => $parentDetails->id,
                        'index' => $detail->category_id
                    ));

                } else{
                    foreach($dataSource as $key => $source) {
                        if($source['id'] === $parentDetails->id && $detail->category_id == $source['category']) {
                            $dataSource[$key]['children'][] = $data;
                        }
                    }
                }
            } elseif($stored) {
                if(count($detail->subDetails)) {
                    $subs = [];

                    foreach($detail->subDetails as $subDetail) {

                        $subDetail->isCascaded = $subDetail->from_aapcr;

                        $subDetail->wasSaved = 1;

                        $subs[] = $this->getVpOpcrDetails($subDetail, []);
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

        $vpOffice = new \stdClass();

        $vpOffice->key = $vpOpcr->office_id;
        $vpOffice->label = $vpOpcr->office_name;

        return response()->json([
            'dataSource' => $dataSource,
            'year' => $vpOpcr->year,
            'vpOffice' => $vpOffice,
            'isFinalized' => $vpOpcr->finalized_date !== NULL,
            'targetsBasisList' => $this->targetsBasisList,
            'editMode' => true,
        ], 200);
    }

    public function getVpOpcrDetails($data, $children)
    {
        $offices = $this->splitPIOffices($data->offices, 1);

        $this->getTargetsBasisList($data->targets_basis);

        $extracted = $this->extractDetails($data);

        if($data->from_aapcr) {
            $id = $data->aapcr_detail_id;
        }/*elseif(!$isCopy) {
            $id = $data->id;
        }*/else{
//            $id = "new";
            $id = $data->id;
        }

        $details = array(
            'key' => $id,
            'id' => $id,
            'category' => $data->category_id,
            'subCategory' => $extracted['subCategory'],
            'program' => $data->program_id,
            'name' => $data->pi_name,
            'isHeader' => false,
            'target' => $data->target,
            'measures' => $extracted['measures'],
            'budget' => $data->allocated_budget,
            'targetsBasis' => $data->targets_basis,
            'implementing' => $offices['implementing'] ?? [],
            'supporting' => $offices['supporting'] ?? [],
            'remarks' => $data->remarks,
            'deleted' => 0,
            'isCascaded' => $data->isCascaded,
            'wasSaved' => $data->wasSaved
        );

        if(count($children)){
            $details['children'][] = $children;
        }

        return $details;
    }
}
