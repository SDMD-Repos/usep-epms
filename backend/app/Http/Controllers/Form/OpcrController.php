<?php

namespace App\Http\Controllers\Form;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAapcr;
use App\Http\Traits\OfficeTrait;
use App\Http\Traits\FormTrait;
use App\OpcrTemplate;
use App\OpcrTemplateDetails;
use App\Program;
use App\SubCategory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OpcrController extends Controller
{
    //
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
            $offices = $this->splitPIOffices($detail->offices);

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

}
