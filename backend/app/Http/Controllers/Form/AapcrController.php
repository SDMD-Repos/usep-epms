<?php

namespace App\Http\Controllers\Form;

use App\Aapcr;
use App\AapcrDetail;
use App\AapcrDetailOffice;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAapcr;
use App\Program;
use App\SubCategory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AapcrController extends Controller
{
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
            $this->login_user->fullName = $this->login_user->firstName . " " . $this->login_user->lastName;

            return $next($request);
        });

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

            $hasSavedAapcr = Aapcr::where('year', $year)->withTrashed()->get();

            if(count($hasSavedAapcr)) {
                $documentName .= " v" . count($hasSavedAapcr);
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
                var_dump('passed');die;
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

            /*$this->saveOffices($values['implementing'], $detail->id, 'implementing');

            $this->saveOffices($values['supporting'], $detail->id, 'supporting');*/

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
        dd($offices);
        foreach($offices as $office){

            $newOffice = new AapcrDetailOffice();

            if(!array_key_exists('children', $office)){
                $newOffice->vp_office_id = $office['parentId'];

                $office_name = $office['acronym'];
            }else{
                $office_name = $office['label'];
            }

            $newOffice->pi_id = $detailId;
            $newOffice->office_type_id = $fieldName;
            $newOffice->cascade_to = $office['cascadeTo'];
            $newOffice->office_id = $office['id'];
            $newOffice->office_name = $office_name;
            $newOffice->create_id = $this->login_user->pmaps_id;
            $newOffice->history = "Created " . Carbon::now() . " by " . $this->login_user->fullName . "\n";

            if(!$newOffice->save()){
                DB::rollBack();
            }
        }
    }
}
