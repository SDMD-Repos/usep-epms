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
use App\Program;
use App\SubCategory;
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

    public function checkSavedTemplate($year)
    {
        $hasSaved = OpcrTemplate::where([
            ['year', $year],
            ['is_active', 1],
            ['deleted_at', null],
        ])->first();

        return response()->json([
            'hasSaved' => $hasSaved !== null
        ], 200);
    }

    public function checkSaved($year)
    {
        $hasSaved = Opcr::where([
            ['year', $year],
            ['is_active', 1],
            ['deleted_at', null],
        ])->first();

        return response()->json([
            'hasSaved' => $hasSaved !== null
        ], 200);
    }

    public function getAllOpcrTemplate()
    {
        $list = OpcrTemplate::select("*", "id as key")->orderBy('created_at', 'ASC')->get();

        return response()->json([
            'list' => $list
        ], 200);
    }

    public function viewTemplate($id)
    {
        $opcrTemplate = OpcrTemplate::with('detailParents.subDetails')->find($id);

        $dataSource = [];

        # Loop through OPCRTemplate details
        foreach($opcrTemplate->detailParents as $detail) {

            $subs = [];

            if(count($detail->subDetails)) {
                foreach($detail->subDetails as $subPI){

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
                    );

                    array_push($subs, $subItem);

                }
            }

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
                );

                if(count($subs)) {
                    $item['children'] = $subs;
                }

                array_push($dataSource, $item);
            }
        }
        # end OPCRtemplate details loop

        return response()->json([
            'dataSource' => $dataSource,
            'year' => $opcrTemplate->year,
            'isFinalized' => $opcrTemplate->finalized_date !== NULL,
            'editMode' => true
        ], 200);
    }

    public function saveTemplate(StoreAapcr $request)
    {

        try{
            $validated = $request->validated();

            $dataSource = $validated['dataSource'];
            $year = $validated['fiscalYear'];
            $documentName = $validated['documentName'];
            $isFinalized = $validated['isFinalized'];

            $finalizedHistory = ($isFinalized ? 'and finalized ' : '');

            $hasSavedOpcrTemplate = OpcrTemplate::where([
                ['year', $year],
                ['is_active', 1],
                ['deleted_at', null],
            ])->get();

            if(count($hasSavedOpcrTemplate)) {
                return response()->json('Unable to save this document. A finalized OPCR Template has been created for the year '.$year.'.', 409);
            }

            DB::beginTransaction();

            $opcrTemplate = new OpcrTemplate();

            $opcrTemplate->year = $year;
            $opcrTemplate->document_name = $documentName;
            $opcrTemplate->finalized_date = ($isFinalized ? Carbon::now() : null);
            $opcrTemplate->create_id = $this->login_user->pmaps_id;
            $opcrTemplate->history = "Created ". $finalizedHistory . Carbon::now() . " by " . $this->login_user->fullName . "\n";

            if ($opcrTemplate->save()) {
                foreach($dataSource as $value) {
                    $this->saveOpcrTemplateDetails($opcrTemplate->id, $value);
                }
            }else{
                DB::rollBack();
            }

            DB::commit();

            return response()->json('OPCR Template was saved successfully', 200);
        }catch(\Exception $e){
            if (is_numeric($e->getCode()) && $e->getCode() && $e->getCode() < 511) {
                $status = $e->getCode();
            } else {
                $status = 400;
            }

            return response()->json($e->getMessage(), $status);
        }
    }

    public function saveOpcrTemplateDetails($opcrTemplateId, $values)
    {

        $detail = new OpcrTemplateDetails();

        if($values['subCategory']){
            $category_id = SubCategory::find($values['subCategory']['value']);
        }else{
            $category_id = Program::find($values['program']);
        }

        $detail->opcr_template_id = $opcrTemplateId;
        $detail->category_id = $category_id->category->id;
        $detail->sub_category_id = $values['subCategory'] ? $values['subCategory']['value'] : $values['subCategory'];
        $detail->program_id = $values['program'];
        $detail->pi_name = $values['name'];
        $detail->is_header = $values['isHeader'];

        if(!$values['isHeader']) {
            $detail->target = $values['target'];
        }

        $detail->parent_id = isset($values['detailId']) ? $values['detailId'] : null;
        $detail->create_id = $this->login_user->pmaps_id;
        $detail->history = "Created " . Carbon::now() . " by " . $this->login_user->fullName . "\n";

        if($detail->save()) {

            $this->saveMeasures($detail, $values['measures']);

            if(isset($values['children']) && count($values['children'])) {

                foreach ($values['children'] as $child) {
                    $child['detailId'] = $detail->id;

                    $this->saveOpcrTemplateDetails($opcrTemplateId, $child);
                }
            }
        }
    }

    public function deactivateTemplate(Request $request)
    {
        $id = $request->id;

        $now = Carbon::now();

        $opcrTemplate = OpcrTemplate::find($id);

        $opcrTemplate->is_active = 0;
        $opcrTemplate->updated_at = $now;
        $opcrTemplate->modify_id = $this->login_user->pmaps_id;
        $opcrTemplate->history = $opcrTemplate->history . "Deactivated " . $now . " by " . $this->login_user->fullName . "\n";

        $opcrTemplate->save();

        return response()->json("Successfully deactivated", 200);
    }

    public function updateTemplate(UpdateOpcrTemplate $request, $id)
    {
        try {

            DB::beginTransaction();

            $validated = $request->validated();

            $dataSource = $validated['dataSource'];
            $year = $validated['fiscalYear'];
            $isFinalized = $validated['isFinalized'];
            $documentName = $validated['documentName'];
            $deletedIds = $validated['deletedIds'];

            $opcrTemplate = OpcrTemplate::find($id);

            $history = "";

            if($isFinalized){
                if($opcrTemplate->finalized_date !== NULL){
                    $finalized_date = $opcrTemplate->finalized_date;
                }else{
                    $finalized_date = Carbon::now();
                    $history = 'Finalized ' . Carbon::now()." by ".$this->login_user->fullName."\n";
                }
            }

            $original = $opcrTemplate->getOriginal();

            $opcrTemplate->year = $year;
            $opcrTemplate->document_name = $documentName;
            $opcrTemplate->finalized_date = $isFinalized ? $finalized_date : null;
            $opcrTemplate->modify_id = $this->login_user->pmaps_id;

            if($opcrTemplate->isDirty('year')){
                $history .= "Updated year from ".$original['year']." to ".$year." ". Carbon::now()." by ".$this->login_user->fullName."\n";
            }

            if($opcrTemplate->isDirty('document_name')){
                $history .= "Updated document name from ".$original['document_name']." to ".$documentName." ". Carbon::now()." by ".$this->login_user->fullName."\n";
            }

            $opcrTemplate->history = $opcrTemplate->history.$history;

            if($opcrTemplate->save()) {
                foreach($deletedIds as $deletedId){
                    $deleteOpcrTemplate = OpcrTemplateDetails::find($deletedId);

                    $deleteOpcrTemplate->modify_id = $this->login_user->pmaps_id;
                    $deleteOpcrTemplate->history = $deleteOpcrTemplate->history."Deleted ". Carbon::now(). " by ".$this->login_user->fullName."\n";

                    if($deleteOpcrTemplate->save()){
                        if(!$deleteOpcrTemplate->delete()){
                            DB::rollBack();
                        }
                    }else{
                        DB::rollBack();
                    }
                }

                foreach($dataSource as $source){
                    $this->updateDetails($source, $id);
                }

                DB::commit();

                return response()->json('OPCR Template was updated successfully', 200);
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

    public function updateDetails($data, $opcrTemplateId)
    {
        if (strpos((string)$data['id'], 'new') === false) {

            $detail = OpcrTemplateDetails::find($data['id']);

            if($detail) {
                if($data['subCategory']){
                    $category_id = SubCategory::find($data['subCategory']['value']);
                }else{
                    $category_id = Program::find($data['program']);
                }

                $original = $detail->getOriginal();

                $isHeader = $data['isHeader'] ? 1 : 0;

                $subCategory = $data['subCategory'] ? $data['subCategory']['value'] : $data['subCategory'];

                $detail->category_id = $category_id->category->id;
                $detail->sub_category_id = $subCategory;
                $detail->program_id = $data['program'];
                $detail->pi_name = $data['name'];
                $detail->is_header = $isHeader;
                $detail->target = $data['target'];

                $history = '';

                if($detail->isDirty('pi_name')){
                    $history .= "Updated Performance Indicator from '".$original['pi_name']."' to '".$data['name']."' ". Carbon::now()." by ".$this->login_user->fullName."\n";
                }

                if($detail->isDirty('is_header')){
                    $history .= "Updated is_header from ".$original['is_header']." to ".$isHeader." ". Carbon::now()." by ".$this->login_user->fullName."\n";
                }

                if($detail->isDirty('target')){
                    $history .= "Updated Target from '".$original['target']."' to '".$data['target']."' ". Carbon::now()." by ".$this->login_user->fullName."\n";
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

                $detail->history = $detail->history.$history;

                if(!$detail->save()){
                    DB::rollBack();
                }

                $this->updateMeasures(new OpcrTemplateDetailsMeasures, $data['id'], $data['measures']);

                if(isset($data['children']) && count($data['children'])) {

                    foreach ($data['children'] as $child) {
                        $child['detailId'] = $detail->id;

                        $this->updateDetails($child, $opcrTemplateId);
                    }
                }

            } else {
                DB::rollBack();
            }
        } else {
            $this->saveOpcrTemplateDetails($opcrTemplateId, $data);
        }
    }

    public function publishTemplate(Request $request)
    {
        $id = $request->id;
        $year = $request->year;

        $hasPublished = OpcrTemplate::where([
            ['year', $year],
            ['is_active', 1]
        ])->whereNotNull('published_date')->first();

        if(!$hasPublished) {
            $opcrTemplate = OpcrTemplate::find($id);

            $opcrTemplate->published_date = Carbon::now();
            $opcrTemplate->updated_at = Carbon::now();
            $opcrTemplate->modify_id = $this->login_user->pmaps_id;
            $opcrTemplate->history = $opcrTemplate->history . "Published " . Carbon::now() . " by " . $this->login_user->fullName . "\n";

            $opcrTemplate->save();

            return response()->json('Opcr Template was published successfully', 200);

        }else{
            return response()->json('Cannot publish two or more Opcr Templates in a year', 400);
        }
    }

    public function unpublishTemplate(Request $request)
    {
        try {
            $id = $request['id'];
            $opcrTemplate = OpcrTemplate::find($id);

            $opcrTemplate->published_date = null;
            $opcrTemplate->history = $opcrTemplate->history . "Unpublished " . Carbon::now() . " by " . $this->login_user->fullName . "\n";
            $opcrTemplate->save();

            return response()->json('OCPR Template was unpublished successfully', 200);
        }catch(\Exception $e){
            if (is_numeric($e->getCode()) && $e->getCode() && $e->getCode() < 511) {
                $status = $e->getCode();
            } else {
                $status = 400;
            }

            return response()->json($e->getMessage(), $status);
        }
    }

}
