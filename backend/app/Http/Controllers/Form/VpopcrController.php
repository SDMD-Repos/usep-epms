<?php

namespace App\Http\Controllers\Form;

use App\Aapcr;
use App\AapcrDetail;
use App\FormField;
use App\FormUnpublishStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVpopcr;
use App\Http\Requests\UnpublishForm;
use App\Http\Requests\UpdateVpOpcr;
use App\Http\Requests\UploadPdfFile;
use App\Http\Traits\ConverterTrait;
use App\Http\Traits\FileTrait;
use App\Http\Traits\FormTrait;
use App\VpOpcr;
use App\VpOpcrDetail;
use App\VpOpcrDetailMeasure;
use App\VpOpcrDetailOffice;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VpopcrController extends Controller
{
    use ConverterTrait, FileTrait, FormTrait;

    private $STO = 5;

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
                        ->orWhere('office_id', '=', $vpId)
                        ->orWhere(function($groupWhere) use ($vpId) {
                            $groupWhere->where('is_group', 1)
                                ->whereHas('group', function($joinQuery) use ($vpId) {
                                    $joinQuery->where('supervising_id', '=', $vpId);
                                });
                        });
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

                $offices = $this->splitPIOffices($data, ['splitOffices' => 1, 'origin' => 'vpopcr']);

                $this->getTargetsBasisList($data->targets_basis);

                $tempCategoryList = [];

                foreach($data->offices as $dataOffice) {
                    if(($dataOffice['vp_office_id'] === $vpId || $dataOffice['office_id'] === $vpId ||
                        ($dataOffice['is_group'] && $dataOffice->group->supervising_id === (int)$vpId))) {

//                        $filteredOffices[] = $dataOffice;

                        $isCategoryExists = array_filter($tempCategoryList, function($x) use ($dataOffice){
                            return $x['id'] === $dataOffice['cascade_to'];
                        });

                        if(!$isCategoryExists) {
                            $tempCategoryList[] = $dataOffice->category;
                        }
                    }
                }

                foreach($tempCategoryList as $categoryList) {
                    $detail = [];

                    $detailIndex = $categoryList->id;

                    if($data->category_id !== $detailIndex){
                        $subCategory = null;
                        $programId = $categoryList->default_program_id;
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

                                    $parentIds[] = array(
                                        'id' => $parent->id,
                                        'index' => $detailIndex
                                    );

                                }else{

                                    $item['type'] = 'pi';

                                    $detail = $item;
                                }
                            }else{
                                foreach($dataSource as $key =>  $source) {
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

                        $parentIds[] = array(
                            'id' => $data->id,
                            'index' => $detailIndex
                        );
                    }

                    if(count($detail)) {
                        $dataSource[] = $detail; // Add new PI to array
                    }
                } # end category list loop
            } # end loop of PI details
        }

        return response()->json([
            'dataSource' => $dataSource,
            'aapcrId' => $query->id ?? null,
            'targetsBasisList' => $this->targetsBasisList,
        ], 200);
    }

    public function getAllVpOpcrs()
    {
        $list = VpOpcr::select("*", "id as key")->with('status')->orderBy('created_at', 'ASC')->get();

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
                        $savedHeaders[] = $source['key'];
                    }

                    $saveDetail = (!$isNew && !isset($source['children'])) || ($isNew) || $storeHeader;

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
        $formFields = FormField::select('id', 'code')->whereIn('code', ['implementing', 'supporting'])->get();

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

            $officeModel = new VpOpcrDetailOffice();
            foreach($formFields as $formField) {
                $this->saveOffices([
                    'form' => 'vpopcr',
                    'model' => $officeModel,
                    'detailId' => $detail->id,
                    'offices' => $data[$formField->code],
                    'fieldName' => $formField->id,
                ]);
            }
        }

        if($isNew){
            return $detail->id;
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

    public function unpublish(UnpublishForm $request)
    {
        try {
            $validated = $request->validated();

            $remarks = $validated['remarks'];
            $id = $validated['id'];
            $officeName = $validated['officeName'];

            DB::beginTransaction();

            $unpublished = new FormUnpublishStatus();

            $unpublished->form_type = 'vpopcr';
            $unpublished->form_id = $id;
            $unpublished->document_name = $officeName;
            $unpublished->remarks = $remarks;
            $unpublished->status = 'pending';
            $unpublished->requested_date = Carbon::now();
            $unpublished->requested_by = $this->login_user->fullName;
            $unpublished->create_id = $this->login_user->pmaps_id;
            $unpublished->history = "Created " . Carbon::now() . " by " . $this->login_user->fullName . "\n";

            if(!$unpublished->save()) {
                DB::rollBack();
            }

            DB::commit();

            return response()->json('OPCR VP was unpublished successfully', 200);
        }catch(\Exception $e){
            if (is_numeric($e->getCode()) && $e->getCode() && $e->getCode() < 511) {
                $status = $e->getCode();
            } else {
                $status = 400;
            }

            return response()->json($e->getMessage(), $status);
        }
    }

//    public function unpublish(UploadPdfFile $request)
//    {
//        try {
//            DB::beginTransaction();
//
//            $validated = $request->validated();
//
//            $files = $validated['files'];
//            $id = $validated['id'];
//
//            $vpopcr = VpOpcr::find($id);
//
//            $vpopcr->published_date = NULL;
//            $vpopcr->updated_at = Carbon::now();
//            $vpopcr->modify_id = $this->login_user->pmaps_id;
//            $vpopcr->history = $vpopcr->history . "Unpublished " . Carbon::now() . " by " . $this->login_user->fullName . "\n";
//
//            if($vpopcr->save()) {
//                $model = new VpOpcrFile();
//
//                $this->uploadFiles($model, $id, $files);
//            }else {
//                DB::rollBack();
//            }
//
//            DB::commit();
//
//            return response()->json('VP\'s OPCR was unpublished successfully', 200);
//        } catch(\Exception $e){
//            if (is_numeric($e->getCode()) && $e->getCode() && $e->getCode() < 511) {
//                $status = $e->getCode();
//            } else {
//                $status = 400;
//            }
//
//            return response()->json($e->getMessage(), $status);
//        }
//    }

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
                        $parentDetails->category_id = $detail->category_id;

                        $parentDetails->sub_category = null;
                    }else{
                        $parentDetails->sub_category = $subCategory;
                    }

                    $data['type'] = 'sub';

                    $parentDetails['type'] = 'pi';

                    $dataSource[] = $this->getVpOpcrDetails($parentDetails, $data);

                    $parentIds[] = array(
                        'id' => $parentDetails->id,
                        'index' => $detail->category_id
                    );

                } else{
                    foreach($dataSource as $key => $source) {
                        if($source['id'] === $parentDetails->id && $detail->category_id == $source['category']) {
                            $data['type'] = 'sub';

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

                        $subDetail->type = 'sub';

                        $subs[] = $this->getVpOpcrDetails($subDetail, []);
                    }

                    $data['children'] = $subs;
                }

                $data['type'] = 'pi';

                $dataSource[] = $data;

                $parentIds[] = array(
                    'id' => $detail->id,
                    'index' => $detail->category_id
                );
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
            'aapcrId' => $vpOpcr->aapcr_id,
            'editMode' => true,
        ], 200);
    }

    public function getVpOpcrDetails($data, $children)
    {
        $offices = $this->splitPIOffices($data->offices, ['splitOffices' => 1, 'origin' => 'vpopcr-view']);

        $this->getTargetsBasisList($data->targets_basis);

        $extracted = $this->extractDetails($data);

        $details = array(
            'key' => $data->id,
            'id' => $data->id,
            'aapcr_detail_id' => $data->aapcr_detail_id,
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
            'wasSaved' => $data->wasSaved
        );

        if(count($children)){
            $details['children'][] = $children;
        }

        return $details;
    }

    public function update(UpdateVpOpcr $request, $id)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validated();

            $dataSource = $validated['dataSource'];
            $isFinalized = $validated['isFinalized'];
            $deletedIds = $validated['deletedIds'];

            $vpOpcr = VpOpcr::find($id);

            $history = "";

            if($isFinalized){
                if($vpOpcr->finalized_date !== NULL){
                    $finalized_date = $vpOpcr->finalized_date;
                }else{
                    $finalized_date = Carbon::now();
                    $history = 'Finalized ' . Carbon::now()." by ".$this->login_user->fullName."\n";
                }
            }

            $vpOpcr->finalized_date = ($isFinalized ? $finalized_date : null);
            $vpOpcr->modify_id = $this->login_user->pmaps_id;

            $vpOpcr->history = $vpOpcr->history.$history;

            if($vpOpcr->save()) {
                foreach($deletedIds as $deletedId) {
                    $deletedVpOpcr = VpOpcrDetail::find($deletedId);

                    $deletedVpOpcr->modify_id = $this->login_user->pmaps_id;
                    $deletedVpOpcr->history = $deletedVpOpcr->history."Deleted ". Carbon::now(). " by ".$this->login_user->fullName."\n";

                    if($deletedVpOpcr->save()){
                        if(!$deletedVpOpcr->delete()){
                            DB::rollBack();
                        }
                    }else{
                        DB::rollBack();
                    }
                }

                foreach($dataSource as $source) {
                    $getParentId = null;

                    $isNew = strpos($source['id'], 'new') !== false;

                    if((isset($source['isCascaded']) && !$source['isCascaded']) || $isNew){
                        $process = 0;

                        if(!$isNew) {
                            $detail = VpOpcrDetail::find($source['id']);

                            if($detail) {
                                $this->updateDetails($source, $detail);
                            } else {
                                $process = 1;
                            }
                        } else{
                            $process = 1;
                        }

                        if($process) {
                            $source['aapcr_detail_id'] = null;

                            $source['from_aapcr'] = 0;

                            $source['parent_id'] = null;

                            $newDetail = 1;

                            $getParentId = $this->saveVpOpcrDetails($id, $source, $newDetail);
                        }
                    } else {
                        if((!isset($source['wasSaved']) || (isset($source['wasSaved']) && !$source['wasSaved'])) && !isset($source['children'])){
                            $hasTrashed = VpOpcrDetail::onlyTrashed()->where([
                                ['aapcr_detail_id', $source['id']],
                                ['from_aapcr', 1],
                                ['vp_opcr_id', $id]
                            ])->first();

                            if($hasTrashed !== null) {
                                $hasTrashed->restore();

                                $hasTrashed->modify_id = $this->login_user->pmaps_id;
                                $hasTrashed->history = $hasTrashed->history."Updated " . Carbon::now() . " by " . $this->login_user->fullName . "\n";

                                if(!$hasTrashed->save()){
                                    DB::rollBack();
                                }
                            }else{
                                $source['aapcr_detail_id'] = $isNew ? null : $source['id'];

                                $source['from_aapcr'] = !$isNew;

                                $source['parent_id'] = null;

                                $newDetail = $isNew;

                                $getParentId = $this->saveVpOpcrDetails($id, $source, $newDetail);
                            }
                        }
                    }

                    if(isset($source['children']) && count($source['children'])) {
                        foreach($source['children'] as $child) {
                            $isChildNew = strpos($child['id'], 'new') !== false;

                            if((isset($child['isCascaded']) && !$child['isCascaded']) || $isChildNew) {
                                $process = 0;

                                if(!$isChildNew) {
                                    $sub = VpOpcrDetail::find($child['id']);

                                    if($sub) {
                                        $this->updateDetails($child, $sub);
                                    } else {
                                        $process = 1;
                                    }
                                } else {
                                    $process = 1;
                                }

                                if($process) {
                                    $fromAapcr = 0;

                                    if(!$isChildNew) {
                                        $aapcrDetailId = $child['id'];
                                    }else{
                                        if(!$isNew && (isset($source['wasSaved']) && $source['wasSaved'])){
                                            $aapcrDetailId = $source['aapcr_detail_id'];
                                        }elseif(!$isNew && $source['isCascaded']){
                                            $aapcrDetailId = $source['id'];
                                        }else{
                                            $aapcrDetailId = null;
                                        }
                                    }

                                    if(!$isNew && (!$source['isCascaded'] || $source['isHeader'])){
                                        $getParentId = $source['id'];
                                    }

                                    $child['aapcr_detail_id'] = $aapcrDetailId;

                                    $child['from_aapcr'] = $fromAapcr;

                                    $child['parent_id'] = $getParentId;

                                    $this->saveVpOpcrDetails($id, $child);
                                }
                            }
                        }

                        if(!$isNew && !$source['isHeader'] && (isset($source['wasSaved']) && $source['wasSaved']) && (isset($source['isCascaded']) && $source['isCascaded'])){
                            $aapcr = VpOpcrDetail::find($source['id']);

                            $aapcr->modify_id = $this->login_user->pmaps_id;
                            $aapcr->history = $aapcr->history."Deleted ". Carbon::now(). " by ".$this->login_user->fullName."\n";

                            if($aapcr->save()){
                                if(!$aapcr->delete()){
                                    DB::rollBack();
                                }
                            }else{
                                DB::rollBack();
                            }
                        }
                    }
                }
            } else {
                DB::rollBack();
            }

            DB::commit();

            return response()->json("VP's OPCR was updated successfully", 200);
        } catch(\Exception $e){
            if (is_numeric($e->getCode()) && $e->getCode() && $e->getCode() < 511) {
                $status = $e->getCode();
            } else {
                $status = 400;
            }

            return response()->json($e->getMessage(), $status);
        }
    }

    public function updateDetails($data, $updated)
    {
        $formFields = FormField::select('id', 'code')->whereIn('code', ['implementing', 'supporting'])->get();

        $original = $updated->getOriginal();

        $isHeader = $data['isHeader'] ? 1 : 0;

        if (!$data['isCascaded']) {
            $subCategory = $data['subCategory'] ? $data['subCategory']['value'] : $data['subCategory'];

            $updated->sub_category_id = $subCategory;
            $updated->program_id = $data['program'];
        }

        $updated->is_header = $isHeader;
        $updated->pi_name = $data['name'];
        $updated->target = $data['target'];
        $updated->targets_basis = $data['targetsBasis'];
        $updated->allocated_budget = $data['budget'];
        $updated->remarks = $data['remarks'];
        $updated->modify_id = $this->login_user->pmaps_id;

        $history = '';

        if($updated->isDirty('is_header')){
            $history .= "Updated is_header column from '".$original['is_header']."' to '".$isHeader."' ". Carbon::now()." by ".$this->login_user->fullName."\n";
        }

        if($updated->isDirty('pi_name')){
            $history .= "Updated Performance Indicator from '".$original['pi_name']."' to '".$data['name']."' ". Carbon::now()." by ".$this->login_user->fullName."\n";
        }

        if($updated->isDirty('target')){
            $history .= "Updated Target from ".$original['target']." to ".$data['target']." ". Carbon::now()." by ".$this->login_user->fullName."\n";
        }

        if($updated->isDirty('allocated_budget')){
            $history .= "Updated Allocated Budget from ".$original['allocated_budget']." to ".$data['budget']." ". Carbon::now()." by ".$this->login_user->fullName."\n";
        }

        if($updated->isDirty('targets_basis')){
            $history .= "Updated Targets Basis from '".$original['targets_basis']."' to '".$data['targetsBasis']."' ". Carbon::now()." by ".$this->login_user->fullName."\n";
        }

        if($updated->isDirty('remarks')){
            $history .= "Updated Remarks from '".$original['remarks']."' to '".$data['remarks']."' ". Carbon::now()." by ".$this->login_user->fullName."\n";
        }

        $updated->history = $updated->history.$history;

        if(!$updated->save()){
            DB::rollBack();
        }

        $this->updateMeasures(new VpOpcrDetailMeasure(), $data['id'], $data['measures']);

        $officeModel = new VpOpcrDetailOffice();

        foreach($formFields as $formField) {
            $this->updateOffices([
                'form' => 'vpopcr',
                'model' => $officeModel,
                'detailId' => $data['id'],
                'offices' => $data[$formField->code],
                'type' => $formField->id,
            ]);
        }
    }

    public function viewUploadedFile($id)
    {
        $model = new VpOpcrFile();

        $file = $this->viewFile($model, $id);

        return response()->download($file['contents'], '', $file['headers']);
    }

    public function updateFile(UploadPdfFile $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validated();

            $fileModel = new VpOpcrFile();

            $formModel = new VpOpcr();

            $params = [
                'fileModel' => $fileModel,
                'formModel' => $formModel,
                'validated' => $validated
            ];

            $data = $this->processUpdateFile($params);

            DB::commit();

            return response()->json([
                'data' => $data
            ], 200);
        } catch(\Exception $e){
            if (is_numeric($e->getCode()) && $e->getCode() && $e->getCode() < 511) {
                $status = $e->getCode();
            } else {
                $status = 400;
            }

            return response()->json($e->getMessage(), $status);
        }
    }
}
