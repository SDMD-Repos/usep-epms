<?php

namespace App\Http\Controllers\Form;

use App\Http\Controllers\Controller;
use App\OpcrTemplate;
use Illuminate\Http\Request;

class TemplateController extends Controller
{
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

    public function opcrView($id)
    {
        try {
            $opcrTemplate = OpcrTemplate::with(['detailParents.subDetails', 'detailParents.measures'])->find($id);

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
                            'category' => $subPI->category_id,
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
                        'category' => $detail->category_id,
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
