<?php

namespace App\Http\Controllers\Form;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAapcr;
use App\Http\Requests\UpdateOpcrTemplate;
use App\Http\Traits\ConverterTrait;
use App\Http\Traits\FormTrait;
use App\Models\OpcrTemplate;
use App\Models\OpcrTemplateDetail;
use App\Models\OpcrTemplateDetailMeasure;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TemplateController extends Controller
{
    use ConverterTrait, FormTrait;

    public function opcrCheckSaved($year)
    {
        try {
            $hasSaved = OpcrTemplate::where([
                ['year', $year],
                ['is_active', 1],
                ['deleted_at', null],
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

    public function getAllOpcr()
    {
        try {
            $list = OpcrTemplate::select("*", "id as key")->orderBy('created_at', 'ASC')->get();

            return response()->json([
                'list' => $list
            ], 200);
        }catch(\Exception $e){
            if (is_numeric($e->getCode()) && $e->getCode() && $e->getCode() < 511) {
                $status = $e->getCode();
            } else {
                $status = 400;
            }

            return response()->json($e->getMessage(), $status);
        }
    }

    public function saveOpcr(StoreAapcr $request)
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

            if(count((array)$hasSavedOpcrTemplate)) {
                return response()->json('Unable to save this document. A finalized OPCR Template has been created for the year '.$year.'.', 409);
            }

            DB::beginTransaction();

            $opcrTemplate = new OpcrTemplate();

            $opcrTemplate->year = $year;
            $opcrTemplate->document_name = $documentName;
            $opcrTemplate->finalized_date = ($isFinalized ? Carbon::now() : null);
            $opcrTemplate->create_id = $this->login_user->pmaps_id;
            $opcrTemplate->history = "Created ". $finalizedHistory . Carbon::now() . " by " . $this->login_user->fullname . "\n";

            if ($opcrTemplate->save()) {
                foreach($dataSource as $value) {
                    $this->saveOpcrDetails($opcrTemplate->id, $value);
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

    public function saveOpcrDetails($opcrTemplateId, $values)
    {

        $detail = new OpcrTemplateDetail();

        $detail->opcr_template_id = $opcrTemplateId;
        $detail->category_id = $values['category'];
        $detail->sub_category_id = isset($values['subCategory']) ? $values['subCategory']['value'] : null;
        $detail->program_id = $values['program'];
        $detail->pi_name = $values['name'];
        $detail->is_header = $values['isHeader'];
        $detail->linked_to_child = $values['linkedToChild'] ?? 0;

        if(!$values['isHeader']) {
            $detail->target = $values['target'];
        }

        $detail->parent_id = isset($values['detailId']) ? $values['detailId'] : null;
        $detail->create_id = $this->login_user->pmaps_id;
        $detail->history = "Created " . Carbon::now() . " by " . $this->login_user->fullname . "\n";

        if($detail->save()) {

            $this->saveMeasures($detail, $values['measures']);

            if(isset($values['children']) && count((array)$values['children'])) {

                foreach ($values['children'] as $child) {
                    $child['detailId'] = $detail->id;

                    $this->saveOpcrDetails($opcrTemplateId, $child);
                }
            }
        }
    }

    public function viewOpcr($id)
    {
        try {
            $opcrTemplate = OpcrTemplate::with(['detailParents.subDetails', 'detailParents.measures'])->find($id);

            $dataSource = [];

            # Loop through OPCRTemplate details
            foreach($opcrTemplate->detailParents as $detail) {

                $subs = [];

                if(count((array)$detail->subDetails)) {
                    foreach($detail->subDetails as $subPI){

                        $extracted = $this->extractDetails($subPI);

                        $subItem = array(
                            'key' => $subPI->id,
                            'id' => $subPI->id,
                            'type' => 'sub',
                            'category' => $subPI->category_id,
                            'subCategory' => $extracted['subCategory'],
                            'program' => $subPI->program_id,
                            'name' => $subPI->pi_name,
                            'isHeader' => (bool)$subPI->is_header,
                            'target' => $subPI->target,
                            'measures' => $extracted['measures'],
                        );

                        $subs[] = $subItem;

                    }
                }

                $extracted = $this->extractDetails($detail);

                if(!$detail->parent_id){
                    $item = array(
                        'key' => $detail->id,
                        'id' => $detail->id,
                        'type' => 'pi',
                        'category' => $detail->category_id,
                        'subCategory' => $extracted['subCategory'],
                        'program' => $detail->program_id,
                        'name' => $detail->pi_name,
                        'isHeader' => (bool)$detail->is_header,
                        'target' => $detail->target,
                        'measures' => $extracted['measures'],
                        'linkedToChild' => $detail->linked_to_child,
                    );

                    if(count((array)$subs)) {
                        $item['children'] = $subs;
                    }

                    $dataSource[] = $item;
                }
            }
            # end OPCRtemplate details loop

            return response()->json([
                'dataSource' => $dataSource,
                'year' => $opcrTemplate->year,
                'isFinalized' => $opcrTemplate->finalized_date !== NULL,
                'editMode' => true
            ], 200);
        }catch(\Exception $e){
            if (is_numeric($e->getCode()) && $e->getCode() && $e->getCode() < 511) {
                $status = $e->getCode();
            } else {
                $status = 400;
            }
            dd($e);

            return response()->json($e->getMessage(), $status);
        }
    }

    public function updateOpcr(UpdateOpcrTemplate $request, $id)
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
                    $history = 'Finalized ' . Carbon::now()." by ".$this->login_user->fullname."\n";
                }
            }

            $original = $opcrTemplate->getOriginal();

            $opcrTemplate->year = $year;
            $opcrTemplate->document_name = $documentName;
            $opcrTemplate->finalized_date = $isFinalized ? $finalized_date : null;
            $opcrTemplate->modify_id = $this->login_user->pmaps_id;

            if($opcrTemplate->isDirty('year')){
                $history .= "Updated year from ".$original['year']." to ".$year." ". Carbon::now()." by ".$this->login_user->fullname."\n";
            }

            if($opcrTemplate->isDirty('document_name')){
                $history .= "Updated document name from ".$original['document_name']." to ".$documentName." ". Carbon::now()." by ".$this->login_user->fullname."\n";
            }

            $opcrTemplate->history = $opcrTemplate->history.$history;

            if($opcrTemplate->save()) {
                foreach($deletedIds as $deletedId){
                    $deleteOpcrTemplate = OpcrTemplateDetail::find($deletedId);

                    $deleteOpcrTemplate->modify_id = $this->login_user->pmaps_id;
                    $deleteOpcrTemplate->history = $deleteOpcrTemplate->history."Deleted ". Carbon::now(). " by ".$this->login_user->fullname."\n";

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

            $detail = OpcrTemplateDetail::find($data['id']);

            if($detail) {
                $original = $detail->getOriginal();

                $isHeader = $data['isHeader'] ? 1 : 0;

                $subCategory = isset($data['subCategory']) ? $data['subCategory']['value'] : null;

                $detail->category_id = $data['category'];
                $detail->sub_category_id = $subCategory;
                $detail->program_id = $data['program'];
                $detail->pi_name = $data['name'];
                $detail->is_header = $isHeader;
                $detail->target = $data['target'];

                $history = '';

                if($detail->isDirty('pi_name')){
                    $history .= "Updated Performance Indicator from '".$original['pi_name']."' to '".$data['name']."' ". Carbon::now()." by ".$this->login_user->fullname."\n";
                }

                if($detail->isDirty('is_header')){
                    $history .= "Updated is_header from ".$original['is_header']." to ".$isHeader." ". Carbon::now()." by ".$this->login_user->fullname."\n";
                }

                if($detail->isDirty('target')){
                    $history .= "Updated Target from '".$original['target']."' to '".$data['target']."' ". Carbon::now()." by ".$this->login_user->fullname."\n";
                }

                if($detail->isDirty('category_id')){
                    $history .= "Updated Category ID from ".$original['category_id']." to ".$category_id->category->id." ". Carbon::now()." by ".$this->login_user->fullname."\n";
                }

                if($detail->isDirty('sub_category_id')){
                    $history .= "Updated Sub Category ID from ".$original['sub_category_id']." to ".$subCategory." ". Carbon::now()." by ".$this->login_user->fullname."\n";
                }

                if($detail->isDirty('program_id')){
                    $history .= "Updated Program ID from ".$original['program_id']." to ".$data['program']." ". Carbon::now()." by ".$this->login_user->fullname."\n";
                }

                $detail->history = $detail->history.$history;

                if(!$detail->save()){
                    DB::rollBack();
                }

                $this->updateMeasures(new OpcrTemplateDetailMeasure, $data['id'], $data['measures']);

                if(isset($data['children']) && count((array)$data['children'])) {

                    foreach ($data['children'] as $child) {
                        $child['detailId'] = $detail->id;

                        $this->updateDetails($child, $opcrTemplateId);
                    }
                }

            } else {
                DB::rollBack();
            }
        } else {
            $this->saveOpcrDetails($opcrTemplateId, $data);
        }
    }
}
