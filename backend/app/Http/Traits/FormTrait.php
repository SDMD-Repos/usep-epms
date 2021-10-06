<?php

namespace App\Http\Traits;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

trait FormTrait {

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

    public function saveMeasures($detail, $measures)
    {
        foreach ($measures as $measure) {
            $detail->measures()->attach($measure['key'], [
                'create_id' => $this->login_user->pmaps_id,
                'history' => "Created " . Carbon::now() . " by " . $this->login_user->fullName . "\n"
            ]);
        }
    }

    public function saveOffices($params=array())
    {
        $model = $params['model'];

        $detailId = $params['detailId'];

        $offices = $params['offices'];

        $fieldName = $params['fieldName'];

        foreach($offices as $office){

            $newOffice = new $model;

            if(isset($office['isGroup']) && $office['isGroup']) {
                $newOffice->is_group = true;
                $newOffice->group_id = $office['value'];
            } else {
                if(!array_key_exists('children', $office)){
                    $newOffice->vp_office_id = $office['pId'];

                    $office_name = $office['acronym'];
                }else{
                    $office_name = $office['label'];
                }

                $newOffice->office_id = $office['value'];
                $newOffice->office_name = $office_name;
            }

            $newOffice->detail_id = $detailId;
            $newOffice->office_type_id = $fieldName;
            $newOffice->cascade_to = $office['cascadeTo'];
            $newOffice->create_id = $this->login_user->pmaps_id;
            $newOffice->history = "Created " . Carbon::now() . " by " . $this->login_user->fullName . "\n";

            $newOffice->save();
        }
    }

    public function updateMeasures($model, $detailId, $measures)
    {
        $measureIds = array();

        foreach($measures as $measure){
            $updatedMeasure = $model::withTrashed()->where([
                'detail_id' => $detailId,
                'measure_id' => $measure['key']
            ])->first();

            if(!isset($updatedMeasure->id)){
                $newMeasure = new $model;

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

        $deletedMeasures = $model::where('detail_id', $detailId)->whereNotIn('id', $measureIds)->get();

        foreach($deletedMeasures as $deletedMeasure) {
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

    public function updateOffices($params=array())
    {
        $officeIds = array();

        $model = $params['model'];
        $detailId = $params['detailId'];
        $offices = $params['offices'];
        $type = $params['type'];

        foreach($offices as $office){

            $updatedOffice = $model::withTrashed()->where([
                'detail_id' => $detailId,
                'office_type_id' => $type
            ])->where(function ($query) use ($office) {
                if (isset($office['isGroup']) && $office['isGroup']) {
                    $query->where('group_id', $office['value']);
                } else {
                    $query->where('office_id', $office['value']);
                }
            })->first();

            if(!isset($updatedOffice->id)){
                $newOffice = new $model;

                $newOffice->detail_id = $detailId;
                $newOffice->office_type_id = $type;
                $newOffice->cascade_to = $office['cascadeTo'];

                if(isset($office['isGroup']) && $office['isGroup']) {
                    $newOffice->is_group = true;
                    $newOffice->group_id = $office['value'];
                } else {
                    if(!array_key_exists('children', $office)){
                        $newOffice->vp_office_id = $office['pId'];

                        $office_name = $office['acronym'];
                    }else{
                        $office_name = $office['label'];
                    }

                    $newOffice->office_id = $office['value'];
                    $newOffice->office_name = $office_name;
                }

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

        $deletedOffices = $model::where([
            'detail_id' => $detailId,
            'office_type_id' => $type
        ])->whereNotIn('id', $officeIds)->get();

        foreach($deletedOffices as $deletedOffice) {
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
}
