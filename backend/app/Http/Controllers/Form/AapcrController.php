<?php

namespace App\Http\Controllers\Form;

use App\Aapcr;
use App\AapcrDetail;
use App\AapcrDetailMeasure;
use App\AapcrDetailOffice;
use App\AapcrFile;
use App\AapcrProgramBudget;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAapcr;
use App\Http\Requests\UpdateAapcr;
use App\Http\Requests\UploadPdfFile;
use App\Http\Traits\ConverterTrait;
use App\Http\Traits\FileTrait;
use App\Program;
use App\SubCategory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class AapcrController extends Controller
{
    use ConverterTrait, FileTrait;

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
        $getSaved = Aapcr::where([
            ['year', $year],
            ['is_active', 1],
        ])->orderBy('created_at', 'DESC')->first();

        return response()->json([
            'hasSaved' => isset($getSaved->id)
        ], 200);
    }

    public function getAllAapcrs()
    {
        $aapcrList = Aapcr::select("*", "id as key")->with('files')->orderBy('created_at', 'ASC')->get();

        return response()->json([
            'aapcrList' => $aapcrList
        ], 200);
    }

    public function save(StoreAapcr $request)
    {
        try{
            $validated = $request->validated();

            $dataSource = $validated['dataSource'];
            $year = $validated['fiscalYear'];
            $documentName = $validated['documentName'];
            $isFinalized = $validated['isFinalized'];
            $programBudgets = $validated['programBudgets'];

            $finalizedHistory = ($isFinalized ? 'and finalized ' : '');

            $hasSavedAapcr = Aapcr::where([
                ['year', $year],
                ['is_active', 1]
            ])->get();

            if(count($hasSavedAapcr)) {
                return response()->json('Unable to save this document. A finalized AAPCR has been created for the year '.$year.'.', 409);
            }

            DB::beginTransaction();

            $aapcr = new Aapcr();

            $aapcr->year = $year;
            $aapcr->document_name = $documentName;
            $aapcr->finalized_date = ($isFinalized ? Carbon::now() : null);
            $aapcr->create_id = $this->login_user->pmaps_id;
            $aapcr->history = "Created ". $finalizedHistory . Carbon::now() . " by " . $this->login_user->fullName . "\n";

            if ($aapcr->save()) {
                foreach($dataSource as $value) {
                    $this->saveAapcrDetails($aapcr->id, $value);
                }

                $this->saveProgramBudgets($aapcr->id, $programBudgets);
            }else{
                DB::rollBack();
            }

            DB::commit();

            return response()->json('AAPCR was saved successfully', 200);
        }catch(\Exception $e){
            if (is_numeric($e->getCode()) && $e->getCode() && $e->getCode() < 511) {
                $status = $e->getCode();
            } else {
                $status = 400;
            }

            return response()->json($e->getMessage(), $status);
        }
    }

    public function saveAapcrDetails($aapcrId, $values)
    {
        $detail = new AapcrDetail();

        if($values['subCategory']){
            $category_id = SubCategory::find($values['subCategory']['value']);
        }else{
            $category_id = Program::find($values['program']);
        }

        $detail->aapcr_id = $aapcrId;
        $detail->category_id = $category_id->category->id;
        $detail->sub_category_id = $values['subCategory'] ? $values['subCategory']['value'] : $values['subCategory'];
        $detail->program_id = $values['program'];
        $detail->pi_name = $values['name'];
        $detail->is_header = $values['isHeader'];

        if(!$values['isHeader']) {
            $detail->target = $values['target'];
            $detail->allocated_budget = $values['budget'];
            $detail->targets_basis = $values['targetsBasis'];
            $detail->cascading_level = $values['cascadingLevel']['key'];
            $detail->other_remarks = $values['otherRemarks'];
        }

        $detail->parent_id = isset($values['detailId']) ? $values['detailId'] : null;
        $detail->create_id = $this->login_user->pmaps_id;
        $detail->history = "Created " . Carbon::now() . " by " . $this->login_user->fullName . "\n";

        if($detail->save()) {

            $this->saveMeasures($detail, $values['measures']);

            $this->saveOffices($values['implementing'], $detail->id, 'implementing');

            $this->saveOffices($values['supporting'], $detail->id, 'supporting');

            if(isset($values['children']) && count($values['children'])) {

                foreach ($values['children'] as $child) {
                    $child['detailId'] = $detail->id;

                    $this->saveAapcrDetails($aapcrId, $child);
                }
            }
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

    public function saveOffices($offices, $detailId, $fieldName)
    {
        foreach($offices as $office){

            $newOffice = new AapcrDetailOffice();

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

            if(!$newOffice->save()){
                DB::rollBack();
            }
        }
    }

    public function saveProgramBudgets($aapcrId, $budgets)
    {
        foreach($budgets as $budget) {
            $newBudget = new AapcrProgramBudget();

            $newBudget->aapcr_id = $aapcrId;
            $newBudget->program_id = $budget['mainCategory']['key'];
            $newBudget->budget = $budget['categoryBudget'];
            $newBudget->create_id = $this->login_user->pmaps_id;
            $newBudget->history = "Created " . Carbon::now() . " by " . $this->login_user->fullName . "\n";

            if(!$newBudget->save()){
                DB::rollBack();
            }
        }
    }

    public function publish(Request $request)
    {
        $id = $request->id;
        $year = $request->year;

        $hasPublished = Aapcr::where([
            ['year', $year],
            ['is_active', 1]
        ])->whereNotNull('published_date')->first();

        if(!$hasPublished) {
            $aapcr = Aapcr::find($id);

            $aapcr->published_date = Carbon::now();
            $aapcr->updated_at = Carbon::now();
            $aapcr->modify_id = $this->login_user->pmaps_id;
            $aapcr->history = $aapcr->history . "Published " . Carbon::now() . " by " . $this->login_user->fullName . "\n";

            $aapcr->save();

            return response()->json('AAPCR was published successfully', 200);

        }else{
            return response()->json('Cannot publish two or more AAPCRs in a year', 400);
        }
    }

    public function unpublish(UploadPdfFile $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validated();

            $files = $validated['files'];
            $id = $validated['id'];

            $aapcr = Aapcr::find($id);

            $aapcr->published_date = NULL;
            $aapcr->updated_at = Carbon::now();
            $aapcr->modify_id = $this->login_user->pmaps_id;
            $aapcr->history = $aapcr->history . "Unpublished " . Carbon::now() . " by " . $this->login_user->fullName . "\n";

            if($aapcr->save()) {
                $model = new AapcrFile();

                $this->uploadFiles($model, $id, $files);
            }else {
                DB::rollBack();
            }

            DB::commit();

            return response()->json('AAPCR was unpublished successfully', 200);
        } catch(\Exception $e){
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
        $id = $request->id;

        $now = Carbon::now();

        $aapcr = Aapcr::find($id);

        $aapcr->is_active = 0;
        $aapcr->end_effectivity = $now;
        $aapcr->updated_at = $now;
        $aapcr->modify_id = $this->login_user->pmaps_id;
        $aapcr->history = $aapcr->history . "Deactivated " . $now . " by " . $this->login_user->fullName . "\n";

        $aapcr->save();

        return response()->json("Successfully deactivated", 200);
    }

    public function view($id)
    {
        $aapcr = Aapcr::with('detailParents.subDetails')->find($id);

        # Fetch program's budget
        $programBudgets = AapcrProgramBudget::where('aapcr_id', $id)->orderBy('program_id', 'ASC')->get();

        $programsSummary = [];

        foreach($programBudgets as $programBudget) {
            $mainCategory = new \stdClass();

            $mainCategory->key = $programBudget->program_id;
            $mainCategory->label = $programBudget->program->name;

            $budget = new \stdClass();

            $budget->categoryBudget = $programBudget->budget;
            $budget->mainCategory = $mainCategory;

            array_push($programsSummary, $budget);
        }
        # end

        $dataSource = [];

        $this->targetsBasisList = []; // Reset $this->targetsBasisList from ConverterTrait

        # Loop through AAPCR details
        foreach($aapcr->detailParents as $detail) {
            $offices = $this->splitPIOffices($detail->offices);

            $implementing = count($offices) ? $offices['implementing'] : [];

            $supporting = $offices['supporting'] ?? [];

            $subs = [];

            if(count($detail->subDetails)) {
                foreach($detail->subDetails as $subPI){

                    $subPIOffices = $this->splitPIOffices($subPI->offices);

                    $subImplementing = $subPIOffices['implementing'];

                    $subSupporting = $subPIOffices['supporting'] ?? [];

                    $this->getTargetsBasisList($subPI->targets_basis);

                    $extracted = $this->extractDetails($subPI);

                    $subItem = array(
                        'key' => $subPI->id,
                        'id' => $subPI->id,
                        'type' => 'sub',
                        'subCategory' => $extracted['subCategory'],
                        'program' => $subPI->program_id,
                        'name' => $subPI->pi_name,
                        'isHeader' => (bool)$subPI->is_header,
                        'target' => $subPI->target,
                        'measures' => $extracted['measures'],
                        'budget' => $subPI->allocated_budget,
                        'targetsBasis' => $subPI->targets_basis,
                        'cascadingLevel' => $extracted['cascadingLevel'],
                        'implementing' => $subImplementing,
                        'supporting' => $subSupporting,
                        'otherRemarks' => $subPI->other_remarks,
                    );

                    array_push($subs, $subItem);

                }
            }

            $this->getTargetsBasisList($detail->targets_basis);

            $extracted = $this->extractDetails($detail);

            if(!$detail->parent_id){
                $item = array(
                    'key' => $detail->id,
                    'id' => $detail->id,
                    'type' => 'pi',
                    'subCategory' => $extracted['subCategory'],
                    'program' => $detail->program_id,
                    'name' => $detail->pi_name,
                    'isHeader' => (bool)$detail->is_header,
                    'target' => $detail->target,
                    'measures' => $extracted['measures'],
                    'budget' => $detail->allocated_budget,
                    'targetsBasis' => $detail->targets_basis,
                    'cascadingLevel' => $extracted['cascadingLevel'],
                    'implementing' => $implementing,
                    'supporting' => $supporting,
                    'otherRemarks' => $detail->other_remarks
                );

                if(count($subs)) {
                    $item['children'] = $subs;
                }

                array_push($dataSource, $item);
            }
        }
        # end AAPCR details loop

        return response()->json([
            'dataSource' => $dataSource,
            'year' => $aapcr->year,
            'isFinalized' => $aapcr->finalized_date !== NULL,
            'targetsBasisList' => $this->targetsBasisList,
            'budgetList' => $programsSummary,
            'editMode' => true
        ], 200);
    }

    public function update(UpdateAapcr $request, $id)
    {
        try {

            DB::beginTransaction();

            $validated = $request->validated();

            $dataSource = $validated['dataSource'];
            $year = $validated['fiscalYear'];
            $isFinalized = $validated['isFinalized'];
            $documentName = $validated['documentName'];
            $programBudgets = $validated['programBudgets'];
            $deletedIds = $validated['deletedIds'];

            $aapcr = Aapcr::find($id);

            $history = "";

            if($isFinalized){
                if($aapcr->finalized_date !== NULL){
                    $finalized_date = $aapcr->finalized_date;
                }else{
                    $finalized_date = Carbon::now();
                    $history = 'Finalized ' . Carbon::now()." by ".$this->login_user->fullName."\n";
                }
            }

            $original = $aapcr->getOriginal();

            $aapcr->year = $year;
            $aapcr->document_name = $documentName;
            $aapcr->finalized_date = $isFinalized ? $finalized_date : null;
            $aapcr->modify_id = $this->login_user->pmaps_id;

            if($aapcr->isDirty('year')){
                $history .= "Updated year from ".$original['year']." to ".$year." ". Carbon::now()." by ".$this->login_user->fullName."\n";
            }

            if($aapcr->isDirty('document_name')){
                $history .= "Updated document name from ".$original['document_name']." to ".$documentName." ". Carbon::now()." by ".$this->login_user->fullName."\n";
            }

            $aapcr->history = $aapcr->history.$history;

            if($aapcr->save()) {
                foreach($deletedIds as $deletedId){
                    $deleteAapcr = AapcrDetail::find($deletedId);

                    $deleteAapcr->modify_id = $this->login_user->pmaps_id;
                    $deleteAapcr->history = $deleteAapcr->history."Deleted ". Carbon::now(). " by ".$this->login_user->fullName."\n";

                    if($deleteAapcr->save()){
                        if(!$deleteAapcr->delete()){
                            DB::rollBack();
                        }
                    }else{
                        DB::rollBack();
                    }
                }

                foreach($dataSource as $source){
                    $this->updateDetails($source, $id);
                }

                $this->updateProgramBudgets($id, $programBudgets);

                DB::commit();

                return response()->json('AAPCR was updated successfully', 200);
            } else {
                DB::rollBack();
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

    public function updateDetails($data, $aapcrId)
    {
        if (strpos((string)$data['id'], 'new') === false) {
            $detail = AapcrDetail::find($data['id']);

            if($detail) {
                if($data['subCategory']){
                    $category_id = SubCategory::find($data['subCategory']['value']);
                }else{
                    $category_id = Program::find($data['program']);
                }

                $original = $detail->getOriginal();

                $subCategory = $data['subCategory'] ? $data['subCategory']['value'] : $data['subCategory'];

                $cascadingLevel = $data['cascadingLevel'] ? $data['cascadingLevel']['key'] : null;

                $detail->category_id = $category_id->category->id;
                $detail->sub_category_id = $subCategory;
                $detail->program_id = $data['program'];
                $detail->pi_name = $data['name'];
                $detail->is_header = $data['isHeader'];
                $detail->target = $data['target'];
                $detail->allocated_budget = $data['budget'];
                $detail->targets_basis = $data['targetsBasis'];
                $detail->cascading_level = $cascadingLevel;
                $detail->other_remarks = $data['otherRemarks'];
                $detail->modify_id = $this->login_user->pmaps_id;

                $history = '';

                if($detail->isDirty('pi_name')){
                    $history .= "Updated Performance Indicator from '".$original['pi_name']."' to '".$data['name']."' ". Carbon::now()." by ".$this->login_user->fullName."\n";
                }

                if($detail->isDirty('is_header')){
                    $history .= "Updated is_header from '".$original['is_header']."' to '".$data['isHeader']."' ". Carbon::now()." by ".$this->login_user->fullName."\n";
                }

                if($detail->isDirty('target')){
                    $history .= "Updated Target from '".$original['target']."' to '".$data['target']."' ". Carbon::now()." by ".$this->login_user->fullName."\n";
                }

                if($detail->isDirty('allocated_budget')){
                    $history .= "Updated Allocated Budget from '".$original['allocated_budget']."' to '".$data['budget']."' ". Carbon::now()." by ".$this->login_user->fullName."\n";
                }

                if($detail->isDirty('targets_basis')){
                    $history .= "Updated Targets Basis from '".$original['targets_basis']."' to '".$data['targetsBasis']."' ". Carbon::now()." by ".$this->login_user->fullName."\n";
                }

                if($detail->isDirty('cascading_level')){
                    $history .= "Updated Cascading Level from '".$original['level_of_cascading']."' to '".$cascadingLevel."' ". Carbon::now()." by ".$this->login_user->fullName."\n";
                }

                if($detail->isDirty('category_id')){
                    $history .= "Updated Category ID from ".$original['category_id']." to ".$category_id->category->id." ". Carbon::now()." by ".$this->login_user->fullName."\n";
                }

                if($detail->isDirty('sub_category_id')){
                    $history .= "Updated Sub Category ID from ".$original['sub_category_id']." to ".$subCategory." ". Carbon::now()." by ".$this->login_user->fullName."\n";
                }

                if($detail->isDirty('program_id')){
                    $history .= "Updated Program ID from ".$original['program_id']." to ".$data['program']." ". Carbon::now()." by ".$this->login_user->fullName."\n";
                }

                if($detail->isDirty('other_remarks')){
                    $history .= "Updated Other Remarks from '".$original['other_remarks']."' to '".$data['otherRemarks']."' ". Carbon::now()." by ".$this->login_user->fullName."\n";
                }

                $detail->history = $detail->history.$history;

                if(!$detail->save()){
                    DB::rollBack();
                }

                $this->updateMeasures($data['id'], $data['measures']);

                $this->updateOffices($data['id'], $data['implementing'], 'implementing');

                $this->updateOffices($data['id'], $data['supporting'], 'supporting');

            } else {
                DB::rollBack();
            }
        } else {
            $this->saveAapcrDetails($aapcrId, $data);
        }
    }

    public function updateMeasures($detailId, $measures)
    {
        $measureIds = array();

        foreach($measures as $measure){
            $updatedMeasure = AapcrDetailMeasure::withTrashed()->where([
                'detail_id' => $detailId,
                'measure_id' => $measure['key']
            ])->first();

            if(!isset($updatedMeasure->id)){
                $newMeasure = new AapcrDetailMeasure;

                $newMeasure->detail_id = $detailId;
                $newMeasure->measure_id = $measure['key'];
                $newMeasure->create_id = $this->login_user->pmaps_id;
                $newMeasure->history = "Created " . Carbon::now() . " by " . $this->login_user->fullName . "\n";

                if(!$newMeasure->save()){
                    DB::rollBack();
                }else{
                    array_push($measureIds, $newMeasure->id);
                }
            }else{
                if($updatedMeasure->trashed()){
                    $updatedMeasure->restore();

                    $updatedMeasure->modify_id = $this->login_user->pmaps_id;
                    $updatedMeasure->history = $updatedMeasure->history."Selected " . Carbon::now() . " by " . $this->login_user->fullName . "\n";

                    if(!$updatedMeasure->save()){
                        DB::rollBack();
                    }
                }

                array_push($measureIds, $updatedMeasure->id);
            }
        }

        $deleteAapcrMeasure = AapcrDetailMeasure::where('detail_id', $detailId)->whereNotIn('id', $measureIds)->get();

        foreach($deleteAapcrMeasure as $deletedMeasure) {
            $deletedMeasure->updated_at = Carbon::now();
            $deletedMeasure->modify_id = $this->login_user->pmaps_id;
            $deletedMeasure->history = $deletedMeasure->history."Unselected " . Carbon::now() . " by " . $this->login_user->fullName . "\n";

            if(!$deletedMeasure->save()){
                DB::rollBack();
            }else{
                if(!$deletedMeasure->delete()){
                    DB::rollBack();
                }
            }
        }
    }

    public function updateOffices($detailId, $offices, $type)
    {
        $officeIds = array();

        foreach($offices as $office){

            $updatedOffice = AapcrDetailOffice::withTrashed()->where([
                'detail_id' => $detailId,
                'office_id' => $office['value'],
                'office_type_id' => $type
            ])->first();

            if(!isset($updatedOffice->id)){
                $newOffice = new AapcrDetailOffice();

                if(!array_key_exists('children', $office)){
                    $newOffice->vp_office_id = $office['pId'];

                    $office_name = $office['acronym'];
                }else{
                    $office_name = $office['label'];
                }

                $newOffice->detail_id = $detailId;
                $newOffice->office_type_id = $type;
                $newOffice->cascade_to = $office['cascadeTo'];
                $newOffice->office_id = $office['value'];
                $newOffice->office_name = $office_name;
                $newOffice->create_id = $this->login_user->pmaps_id;
                $newOffice->history = "Created " . Carbon::now() . " by " . $this->login_user->fullName . "\n";

                if(!$newOffice->save()){
                    DB::rollBack();
                }else{
                    array_push($officeIds, $newOffice->id);
                }
            }else{
                $history = '';

                if($updatedOffice->trashed()){
                    $updatedOffice->restore();

                    $history = "Selected " . Carbon::now() . " by " . $this->login_user->fullName . "\n";

                }else{
                    $oldCascadeTo = $updatedOffice->cascade_to;

                    $updatedOffice->cascade_to = $office['cascadeTo'];

                    if($updatedOffice->isDirty('cascade_to')) {

                        $history = "Updated cascade_to from '".$oldCascadeTo."' to '".$office['cascadeTo']. "' ". Carbon::now() . " by " . $this->login_user->fullName . "\n";
                    }
                }

                $updatedOffice->modify_id = $this->login_user->pmaps_id;
                $updatedOffice->history = $updatedOffice->history.$history;

                if(!$updatedOffice->save()){
                    DB::rollBack();
                }

                array_push($officeIds, $updatedOffice->id);
            }
        }

        $deleteAapcrOffice = AapcrDetailOffice::where([
            'detail_id' => $detailId,
            'office_type_id' => $type
        ])->whereNotIn('id', $officeIds)->get();

        foreach($deleteAapcrOffice as $deletedOffice) {
            $deletedOffice->updated_at = Carbon::now();
            $deletedOffice->modify_id = $this->login_user->pmaps_id;
            $deletedOffice->history = $deletedOffice->history."Unselected " . Carbon::now() . " by " . $this->login_user->fullName . "\n";

            if(!$deletedOffice->save()){
                DB::rollBack();
            }else{
                if(!$deletedOffice->delete()){
                    DB::rollBack();
                }
            }
        }
    }

    public function updateProgramBudgets($aapcrId, $budgets)
    {
        $ids = array();

        foreach ($budgets as $budget) {

            $programBudget = AapcrProgramBudget::withTrashed()->where([
                'aapcr_id' => $aapcrId,
                'program_id' => $budget['mainCategory']['key']
            ])->first();

            if(!isset($programBudget->id)){
                $newBudget = new AapcrProgramBudget();

                $newBudget->aapcr_id = $aapcrId;
                $newBudget->program_id = $budget['mainCategory']['key'];
                $newBudget->budget = $budget['categoryBudget'];
                $newBudget->create_id = $this->login_user->pmaps_id;
                $newBudget->history = "Created " . Carbon::now() . " by " . $this->login_user->fullName . "\n";

                if(!$newBudget->save()){
                    DB::rollBack();
                }else{
                    array_push($ids, $newBudget->id);
                }
            }else{
                $history = "";

                if($programBudget->trashed()){
                    $programBudget->restore();
                    $history = "Added " . Carbon::now() . " by " . $this->login_user->fullName . "\n";
                }

                $cachedBudget = $programBudget->budget;

                $programBudget->budget = $budget['categoryBudget'];

                if($programBudget->isDirty('budget')) {
                    $history = "Updated budget from '" . $cachedBudget . "' to '" . $budget['categoryBudget'] . "' " . Carbon::now() . " by " . $this->login_user->fullName . "\n";
                }

                $programBudget->modify_id = $this->login_user->pmaps_id;
                $programBudget->history = $programBudget->history.$history;

                if(!$programBudget->save()){
                    DB::rollBack();
                }

                array_push($ids, $programBudget->id);
            }
        }

        $deletedIds = AapcrProgramBudget::where('aapcr_id', $aapcrId)->whereNotIn('id', $ids)->get();

        foreach($deletedIds as $deletedId) {
            $deletedId->updated_at = Carbon::now();
            $deletedId->modify_id = $this->login_user->pmaps_id;
            $deletedId->history = $deletedId->history."Removed " . Carbon::now() . " by " . $this->login_user->fullName . "\n";

            if(!$deletedId->save()){
                DB::rollBack();
            }else{
                if(!$deletedId->delete()){
                    DB::rollBack();
                }
            }
        }
    }

    public function viewUploadedFile($id)
    {
        $model = new AapcrFile();

        $file = $this->viewFile($model, $id);

        return response()->download($file['contents'], '', $file['headers']);
    }

    public function updateFile(UploadPdfFile $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validated();

            $fileModel = new AapcrFile();

            $formModel = new Aapcr();

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
