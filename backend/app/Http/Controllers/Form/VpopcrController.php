<?php

namespace App\Http\Controllers\Form;

use App\Models\Aapcr;
use App\Models\AapcrDetail;
use App\Models\FormField;
use App\Models\FormUnpublishStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVpopcr;
use App\Http\Requests\UnpublishForm;
use App\Http\Requests\UpdateVpOpcr;
use App\Http\Traits\FileTrait;
use App\Http\Traits\FormTrait;
use App\Http\Traits\OfficeTrait;
use App\Http\Traits\PdfTrait;
use App\Models\VpOpcr;
use App\Models\VpOpcrDetail;
use App\Models\VpOpcrDetailMeasure;
use App\Models\VpOpcrDetailOffice;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VpopcrController extends Controller
{
    use OfficeTrait, PdfTrait, FileTrait, FormTrait;

    private $STO = 5;

    protected $IDs = [];

    public function checkSaved($officeId, $year)
    {
        try {
            $hasSaved = VpOpcr::where([
                ['office_id', (int)$officeId],
                ['year', $year],
                ['is_active', 1]
            ])->first();

            return response()->json([
                'hasSaved' => $hasSaved !== null
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

    public function getAapcrDetails($vpId, $year)
    {
        $query = Aapcr::where([
            ['year', $year],
            ['is_active', 1]
        ])->where(function($columnQuery) {
            $columnQuery->whereNotNull('published_date')->orWhereNotNull('finalized_date');
        })->with(['detailsOrdered' => function ($que) use ($vpId) {
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
        }, 'detailsOrdered.offices' => function ($queryOffice) use ($vpId) {
            $queryOffice->where(function($officeQ) use ($vpId) {
                $officeQ->where('vp_office_id', '=', $vpId)
                    ->orWhere('office_id', '=', $vpId)
                    ->orWhere(function($groupOfcWhere) use ($vpId) {
                        $groupOfcWhere->where('is_group', 1)
                            ->whereHas('group', function($joinOfcQuery) use ($vpId) {
                                $joinOfcQuery->where('supervising_id', '=', $vpId);
                            });
                    });
            });
        }, 'detailsOrdered.measures'])
            ->orderBy('published_date', 'DESC')->orderBy('created_at', 'DESC')->first();

        $dataSource = [];

        $this->targetsBasisList = []; // Reset $this->targetsBasisList from ConverterTrait

        $parentIds = []; // To store parent PIs' id for tracking purposes

        $detailsOrdered = $query ? $query->detailsOrdered : null;

        if($detailsOrdered) {
            $savedIndicators = $this->getSavedIndicators($detailsOrdered, $vpId);
        }

        if ($query) {
            # Loop through PI details
            foreach($detailsOrdered as $data) {
                $detail = [];

                $programId = $data->program_id;

                $programLabel = $data->program_id ? $data->program->name : null;

                $extracted = $this->extractDetails($data);

                $subCategory = $extracted['subCategory'];

                $offices = $this->processVpOffices($data, 'vpopcr');

                $this->getTargetsBasisList($data->targets_basis);

                $tempCategoryList = [];

                foreach($data->offices as $dataOffice) {
                    if(($dataOffice['vp_office_id'] === $vpId || $dataOffice['office_id'] === $vpId ||
                        ($dataOffice['is_group'] && $dataOffice->group->supervising_id === (int)$vpId))) {

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
                        $programLabel = $categoryList->default_program_id ? $categoryList->defaultProgram->name : null;
                    }

                    $this->getTargetsBasisList($data->targets_basis);

                    $item = array(
                        'key' => $data->id,
                        'id' => $data->id,
                        'category' => $detailIndex,
                        'subCategory' => $subCategory,
                        'program' => $programId,
                        'programLabel' => $programLabel,
                        'name' => $data->pi_name,
                        'isHeader' => false,
                        'target' => $data->target,
                        'measures' => $extracted['measures'],
                        'budget' => $data->allocated_budget,
                        'targetsBasis' => $data->targets_basis,
                        'cascadingLevel' => $extracted['cascadingLevel'],
                        'implementing' => $offices['implementing'] ?? [],
                        'supporting' => $offices['supporting'] ?? [],
                        'remarks' => $data->other_remarks,
                        'linkedToChild' => $data->linked_to_child,
                        'deleted' => 0,
                        'isCascaded' => 1
                    );

                    if($data->parent_id) {
                        $parent = AapcrDetail::where('id', $data->parent_id)
                            ->with(['offices' => function ($queryOffice) use ($vpId) {
                                $queryOffice->where(function($officeQ) use ($vpId) {
                                    $officeQ->where('vp_office_id', '=', $vpId)
                                        ->orWhere('office_id', '=', $vpId)
                                        ->orWhere(function($groupOfcWhere) use ($vpId) {
                                            $groupOfcWhere->where('is_group', 1)
                                                ->whereHas('group', function($joinOfcQuery) use ($vpId) {
                                                    $joinOfcQuery->where('supervising_id', '=', $vpId);
                                                });
                                        });
                                });
                            }])->first();

                        if($parent) {
                            $isExists = 0;

                            foreach($parentIds as $id) {
                                if($id['id'] === $parent->id && $detailIndex === $id['index']) {
                                    $isExists = 1;
                                }
                            }

                            if(!$isExists){
                                if($parent->is_header || $parent->linked_to_child) {
                                    $target = ''; $measures = []; $budget = 0.00;
                                    $targetsBasis = ""; $cascadingLevel = ""; $remarks = "";
                                    $linkedToChild = 0; $parentImplementing = []; $parentSupporting = [];

                                    if($parent->linked_to_child) {
                                        $target = $parent->target;
                                        $measures = $this->extractMeasures($parent->measures);
                                        $budget = $parent->budget;
                                        $targetsBasis = $parent->targets_basis;
                                        $cascadingLevel = ['key' => $parent->cascading_level, 'label' => ucwords($parent->cascading_level)];
                                        $remarks = $parent->remarks;
                                        $linkedToChild = $parent->linked_to_child;

                                        $parentOffices = $this->processVpOffices($parent, 'vpopcr');

                                        $parentImplementing = $parentOffices['implementing'] ?? [];
                                        $parentSupporting = $parentOffices['supporting'] ?? [];
                                    }

                                    $detail = array(
                                        'key' => $parent->id,
                                        'id' => $parent->id,
                                        'type' => 'pi',
                                        'category' => $detailIndex,
                                        'subCategory' => $subCategory,
                                        'program' => $programId,
                                        'programLabel' => $programLabel,
                                        'name' => $parent->pi_name,
                                        'isHeader' => true,
                                        'target' => $target,
                                        'measures' => $measures,
                                        'budget' => $budget,
                                        'targetsBasis' => $targetsBasis,
                                        'cascadingLevel' => $cascadingLevel,
                                        'implementing' => $parentImplementing,
                                        'supporting' => $parentSupporting,
                                        'remarks' => $remarks,
                                        'children' => [],
                                        'linkedToChild' => $linkedToChild,
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
            'isPublished' => $query && (bool)$query->published_date,
            'savedIndicators' => $savedIndicators ?? null
        ], 200);
    }

    public function getAllVpOpcrs()
    {
        try {
            $list = VpOpcr::select("*", "id as key")->with('status')->orderBy('created_at', 'ASC')->get();

            return response()->json([
                'list' => $list
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
            $vpopcr->history = "Created ". $finalizedHistory . Carbon::now() . " by " . $this->login_user->fullname . "\n";

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

                    if(($source['isHeader'] || (isset($source['linkedToChild']) && $source['linkedToChild']))
                        && !in_array($source['key'], $savedHeaders)) {

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

            $message = $e->getMessage();

            if($e->getLine()) {
                $message .= " (Line: ". $e->getLine() .")";
            }

            return response()->json($message, $status);
        }
    }

    protected function saveVpOpcrDetails($id, $data, $isNew=0)
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
        $detail->cascading_level = $data['cascadingLevel'] ? $data['cascadingLevel']['key'] : null;
        $detail->category_id = $data['category'];
        $detail->sub_category_id = isset($data['subCategory']) ? $data['subCategory']['value'] : null;
        $detail->program_id = $data['program']['key'] ?? $data['program'];
        $detail->remarks = $data['remarks'];
        $detail->parent_id = $data['parent_id'];
        $detail->from_aapcr = $data['from_aapcr'];
        $detail->linked_to_child = $data['linkedToChild'] ?? 0;
        $detail->create_id = $this->login_user->pmaps_id;
        $detail->history = "Created " . Carbon::now() . " by " . $this->login_user->fullname . "\n";

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
        try {
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
                $vpopcr->history = $vpopcr->history . "Published " . Carbon::now() . " by " . $this->login_user->fullname . "\n";

                $filename = $this->viewVpOpcrPdf($id, 1);

                $vpopcr->published_file = $filename;

                $vpopcr->save();

                return response()->json('OPCR was published successfully', 200);
            }else{
                return response()->json('Cannot publish two or more OPCRs for '.$hasPublished->office_name.' in a year', 400);
            }
        } catch(\Exception $e){
            if (is_numeric($e->getCode()) && $e->getCode() && $e->getCode() < 511) {
                $status = $e->getCode();
            } else {
                $status = 400;
            }

            return response()->json($e->getMessage(), $status);
        }
    }

    public function unpublish(UnpublishForm $request)
    {
        try {
            $validated = $request->validated();

            $remarks = $validated['remarks'];
            $id = $validated['id'];
            $officeName = $validated['officeName'];
            $fileName = $validated['fileName'];

            DB::beginTransaction();

            $unpublished = new FormUnpublishStatus();

            $unpublished->form_type = 'vpopcr';
            $unpublished->form_id = $id;
            $unpublished->document_name = $officeName;
            $unpublished->remarks = $remarks;
            $unpublished->status = 'pending';
            $unpublished->requested_date = Carbon::now();
            $unpublished->requested_by = $this->login_user->fullname;
            $unpublished->file_name = $fileName;
            $unpublished->create_id = $this->login_user->pmaps_id;
            $unpublished->history = "Created " . Carbon::now() . " by " . $this->login_user->fullname . "\n";

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

    public function deactivate(Request $request)
    {
        try {
            $id = $request->id;

            $now = Carbon::now();

            $vpopcr = VpOpcr::find($id);

            $vpopcr->is_active = 0;
            $vpopcr->end_effectivity = $now;
            $vpopcr->updated_at = $now;
            $vpopcr->modify_id = $this->login_user->pmaps_id;
            $vpopcr->history = $vpopcr->history . "Deactivated " . $now . " by " . $this->login_user->fullname . "\n";

            $vpopcr->save();

            return response()->json("Successfully deactivated", 200);
        } catch(\Exception $e){
            if (is_numeric($e->getCode()) && $e->getCode() && $e->getCode() < 511) {
                $status = $e->getCode();
            } else {
                $status = 400;
            }

            return response()->json($e->getMessage(), $status);
        }
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

        $details = $vpOpcr->details()
            ->orderBy('category_id', 'ASC')
            ->orderByRaw('-program_id DESC')
            ->orderByRaw('!ISNULL(sub_category_id), sub_category_id ASC')
            ->orderBy('created_at', 'ASC')->get();

        $savedIndicators = $this->getSavedIndicators($details, $officeId,'view');

        foreach($details as $detail) {
            if(!$detail->parent_id) {
                $stored = 0;

                $isParent = 0;

                $parentDetails = null;

                $detail->isCascaded = $detail->from_aapcr;

                $detail->wasSaved = 1;

                $data = $this->getVpOpcrDetails($detail, []);

                if($detail->aapcr_detail_id) {
                    $aapcrDetail = $detail->aapcrDetail()->with(['offices' => function($q) use ($officeId){
                        $q->filterVpOffices($officeId);
                    }])->first();

                    if($aapcrDetail->parent_id && $detail->from_aapcr) {
                        if(!$detail->aapcrDetail->parent->is_header) {
                            $parentDetail = AapcrDetail::whereHas('offices', function($query) use ($officeId) {
                                $query->filterVpOffices($officeId);
                            })->with(['measures', 'offices' => function ($queryOffice) use ($officeId) {
                                $queryOffice->filterVpOffices($officeId);
                            }])->where('id', $aapcrDetail->parent_id)->first();

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

                            $parentDetails->program_id = $detail->program_id;

                            $parentDetails->sub_category_id = null;
                        }else{
                            $parentDetails->sub_category_id = $detail->sub_category_id;
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
            } // end if ($details->parent_id)
        } // end foreach details

        $vpOffice = new \stdClass();

        $vpOffice->key = $vpOpcr->office_id;
        $vpOffice->label = $vpOpcr->office_name;

        return response()->json([
            'dataSource' => $dataSource,
            'year' => $vpOpcr->year,
            'vpOffice' => $vpOffice,
            'isFinalized' => $vpOpcr->finalized_date !== NULL,
            'targetsBasisList' => $this->targetsBasisList,
            'savedIndicators' => $savedIndicators,
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
            'program' => $extracted['program'],
            'name' => $data->pi_name,
            'isHeader' => (bool)$data->is_header,
            'target' => $data->target,
            'measures' => $extracted['measures'],
            'budget' => $data->allocated_budget,
            'targetsBasis' => $data->targets_basis,
            'cascadingLevel' => $extracted['cascadingLevel'],
            'implementing' => $offices['implementing'] ?? [],
            'supporting' => $offices['supporting'] ?? [],
            'remarks' => $data->remarks,
            'linkedToChild' => $data->linked_to_child,
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
                    $history = 'Finalized ' . Carbon::now()." by ".$this->login_user->fullname."\n";
                }
            }

            $vpOpcr->finalized_date = ($isFinalized ? $finalized_date : null);
            $vpOpcr->modify_id = $this->login_user->pmaps_id;

            $vpOpcr->history = $vpOpcr->history.$history;

            if($vpOpcr->save()) {
                foreach($deletedIds as $deletedId) {
                    $deletedVpOpcr = VpOpcrDetail::find($deletedId);

                    $deletedVpOpcr->modify_id = $this->login_user->pmaps_id;
                    $deletedVpOpcr->history = $deletedVpOpcr->history."Deleted ". Carbon::now(). " by ".$this->login_user->fullname."\n";

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
                                $hasTrashed->history = $hasTrashed->history."Updated " . Carbon::now() . " by " . $this->login_user->fullname . "\n";

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
                        }else {
                            if((isset($source['isCascaded']) && $source['isCascaded']) && $source['wasSaved']) {
                                $this->updateVpOffices($source);
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
                            $aapcr->history = $aapcr->history."Deleted ". Carbon::now(). " by ".$this->login_user->fullname."\n";

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
        $original = $updated->getOriginal();

        $isHeader = $data['isHeader'] ? 1 : 0;

        if (!$data['isCascaded']) {
            $subCategory = isset($data['subCategory']) ? $data['subCategory']['value'] : null;

            $updated->sub_category_id = $subCategory;
            $updated->program_id = $data['program']['key'] ?? $data['program'];

        }

        $updated->is_header = $isHeader;
        $updated->pi_name = $data['name'];
        $updated->target = $data['target'];
        $updated->targets_basis = $data['targetsBasis'];
        $updated->cascading_level = $data['cascadingLevel'] ? $data['cascadingLevel']['key'] : null;
        $updated->allocated_budget = $data['budget'];
        $updated->remarks = $data['remarks'];
        $updated->linked_to_child = $data['linkedToChild'];
        $updated->modify_id = $this->login_user->pmaps_id;

        $history = '';

        if($updated->isDirty('is_header')){
            $history .= "Updated is_header column from '".$original['is_header']."' to '".$isHeader."' ". Carbon::now()." by ".$this->login_user->fullname."\n";
        }

        if($updated->isDirty('pi_name')){
            $history .= "Updated Performance Indicator from '".$original['pi_name']."' to '".$data['name']."' ". Carbon::now()." by ".$this->login_user->fullname."\n";
        }

        if($updated->isDirty('target')){
            $history .= "Updated Target from ".$original['target']." to ".$data['target']." ". Carbon::now()." by ".$this->login_user->fullname."\n";
        }

        if($updated->isDirty('allocated_budget')){
            $history .= "Updated Allocated Budget from ".$original['allocated_budget']." to ".$data['budget']." ". Carbon::now()." by ".$this->login_user->fullname."\n";
        }

        if($updated->isDirty('targets_basis')){
            $history .= "Updated Targets Basis from '".$original['targets_basis']."' to '".$data['targetsBasis']."' ". Carbon::now()." by ".$this->login_user->fullname."\n";
        }

        if($updated->isDirty('remarks')){
            $history .= "Updated Remarks from '".$original['remarks']."' to '".$data['remarks']."' ". Carbon::now()." by ".$this->login_user->fullname."\n";
        }

        $updated->history = $updated->history.$history;

        if(!$updated->save()){
            DB::rollBack();
        }

        $this->updateMeasures(new VpOpcrDetailMeasure(), $data['id'], $data['measures']);

        $this->updateVpOffices($data);
    }

    public function updateVpOffices($data)
    {
        $formFields = FormField::select('id', 'code')->whereIn('code', ['implementing', 'supporting'])->get();

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

    public function getSavedIndicators($indicators, $officeId, $mode='create')
    {
        if($mode === 'view') {
            $detailIds = $indicators->pluck('aapcr_detail_id');
        } else {
            $detailIds = $indicators->pluck('id');
        }

        $params = [
            'detailIds' => $detailIds, 'officeId' => $officeId
        ];

        $list = $this->querySavedIndicators($params);

        return $list;
    }

    public function checkSavedIndicators(Request $request)
    {
        try {
            $indicators = $request->data;
            $officeId = $request->officeId;

            $duplicates = [];

            $this->getIndicatorsByField($indicators);

            $params = [
                'detailIds' => $this->IDs, 'officeId' => $officeId
            ];

            $savedIndicators = $this->querySavedIndicators($params);

            if($savedIndicators) {
                $this->recursiveIndicatorsChecking($indicators, $savedIndicators, $duplicates);
            }

            return response()->json([
                'savedIndicators' => $savedIndicators,
                'dataSource' => $indicators,
                'duplicates' => $duplicates ?? [],
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

    protected function recursiveIndicatorsChecking(&$data, $savedIndicators, &$storage)
    {
        foreach($data as $key => $datum) {
            $offices = [];

            foreach ($datum['implementing'] as $implementing) {
                $offices[] = $implementing['value'];
            }

            foreach ($datum['supporting'] as $supporting) {
                $offices[] = $supporting['value'];
            }

            foreach ($savedIndicators as $savedIndicator) {
                if($datum['key'] === $savedIndicator['aapcr_detail_id']) {
                    $officesArray = $savedIndicator->offices->toArray();

                    $officesSearch = array_filter($officesArray, function($x) use ($offices) {
                        return in_array((int)$x['office_id'], $offices);
                    });

                    if(count($officesSearch)) {
                        $data[$key]['hasError'] = true;
                        $errors = [];

                        foreach ($officesSearch as $search ) {
                            $vpOfficeName = $savedIndicator->vpOpcr->office_name;
                            $assignedField = $search['field']['code'];

                            if(count($data[$key][$assignedField])) {
                                $data[$key][$assignedField] = array_filter($data[$key][$assignedField], function($x) use ($search) {
                                    return $x['value'] !== (int)$search['office_id'];
                                });
                            }

                            $errors[] = [
                                'vpOffice' => $vpOfficeName,
                                'officeId' => $search['office_id'],
                                'officeName' => $search['office_name'],
                                'field' => ucfirst($assignedField),
                                'msg' => $vpOfficeName . " already set " . $search['office_name'] . " as " . ucfirst($assignedField),
                                'solved' => false
                            ];
                        }

                        $storage[] =  [
                            'key' => $key,
                            'id' => $datum['id'],
                            'name' => $datum['name'],
                            'errors' => $errors,
                        ];
                    }
                }
            }

            if(isset($datum['children']) && count($datum['children'])) {
                $this->recursiveIndicatorsChecking($datum['children'], $savedIndicators, $storage);
            }
        }
    }

    protected function querySavedIndicators($params=[])
    {
        extract($params);

        $list = VpOpcrDetail::whereIn('aapcr_detail_id', $detailIds)
            ->whereHas('vpopcr', function($q) use ($officeId) {
                $q->where('office_id', '<>', $officeId);
            })->where('is_header', 0)->with(['offices', 'offices.field', 'vpopcr'])->get();

        return $list;
    }

    protected function getIndicatorsByField($data)
    {
        foreach ($data as $datum) {
            if (strpos($datum['key'], 'new') === false) {
                if($datum['isCascaded'] && !$datum['isHeader']) {
                    if(!isset($datum['aapcr_detail_id'])) {
                        $this->IDs[] = $datum['id'];
                    }elseif($datum['aapcr_detail_id']){
                        $this->IDs[] = $datum['aapcr_detail_id'];
                    }
                }

                if(isset($datum['children']) && count($datum['children'])) {
                    $this->getIndicatorsByField($datum['children']);
                }
            }
        }
    }
}
