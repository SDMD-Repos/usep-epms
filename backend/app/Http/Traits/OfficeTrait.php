<?php

namespace App\Http\Traits;

use Illuminate\Support\Facades\Http;

trait OfficeTrait {
    use ConverterTrait;

    public function getMainOfficesOnly($officesOnly=0)
    {
        try {
            $values = array();

            if(!$officesOnly) {
                $data = new \stdClass();

                $data->id = "allColleges";
                $data->value = "allColleges";
                $data->title = "All Colleges";

                array_push($values, $data);
            }

            $response = HTTP::post('https://hris.usep.edu.ph/hris/api/epms/department', [
                'token' => env('DATA_HRIS_API_TOKEN')
            ]);

            $vpoffices = json_decode($response->body());

            if(count($vpoffices)) {
                foreach ($vpoffices as $vpoffice) {

                    $data = new \stdClass();

                    $data->id = $vpoffice->id;
                    $data->value = $vpoffice->id;
                    $data->title = $vpoffice->Department;

                    array_push($values, $data);
                }

                if(empty($params)){
                    return response()->json([
                        'mainOffices' => $values
                    ], 200);
                }else{
                    return $values;
                }
            }
        }catch (\Exception $e) {
            return response()->json($e->getMessage(), 400);
        }
    }

    public function getMainOfficesWithChildren($nodeStatus, $params=array())
    {
        $status = json_decode($nodeStatus);
        $checkable = null;
        $selectable = null;

        if(isset($status->checkable)) {
            $checkable = $status->checkable;
        } elseif(isset($status->selectable)) {
            $selectable = $status->selectable;
        }

        try {
            $values = array();

            $data = new \stdClass();

            $data->id = "allColleges";
            $data->value = "allColleges";
            $data->title = "All Colleges";
            $data->cascadeTo = null;
            $data->children = $this->getChildOffices("allColleges",1);

            if($checkable) {
                $data->checkable = $checkable->allColleges;
            } elseif($selectable) {
                $data->selectable = $selectable->allColleges;
            }
            array_push($values, $data);

            $response = HTTP::post('https://hris.usep.edu.ph/hris/api/epms/department', [
                'token' => env('DATA_HRIS_API_TOKEN')
            ]);

            $vpoffices = json_decode($response->body());

            if(count($vpoffices)) {
                foreach ($vpoffices as $vpoffice) {

                    $data = new \stdClass();

                    $data->id = $vpoffice->id;
                    $data->value = $vpoffice->id;
                    $data->title = $vpoffice->Acronym;
                    $data->cascadeTo = null;
                    $data->children = $this->getChildOffices($vpoffice->id,1);

                    if($checkable) {
                        $data->checkable = $checkable->mains;
                    } elseif($selectable) {
                        $data->selectable = $selectable->mains;
                    }

                    array_push($values, $data);
                }

                if(empty($params)){
                    return response()->json([
                        'mainOffices' => $values
                    ], 200);
                }else{
                    return $values;
                }
            }
        }catch (\Exception $e) {
            return response()->json($e->getMessage(), 400);
        }
    }

    public function getChildOffices($vp_id, $update=0)
    {
        try {
            if($vp_id === 'allColleges'){
                $response = HTTP::post('https://hris.usep.edu.ph/hris/api/epms/college', [
                    'token' => env('DATA_HRIS_API_TOKEN')
                ]);
            }else{
                $response = HTTP::post('https://hris.usep.edu.ph/hris/api/epms/structure', [
                    "department_id" => (int)$vp_id,
                    "token" => env("DATA_HRIS_API_TOKEN")
                ]);

            }

            $obj = json_decode($response->body());

            $values = array();

            if(count($obj)){
                foreach($obj as $o) {
                    $data = new \stdClass();

                    $data->id = $o->id;
                    $data->value = $o->id;
                    $data->title = $o->Department;
                    $data->acronym = $o->Acronym;
                    $data->pId = $vp_id;
                    $data->cascadeTo = null;

                    array_push($values, $data);
                }
            }

            if($update){
                return $values;
            }else{
                return response()->json([
                    'childOffices' => $values
                ], 200);
            }
        }catch(\Exception $e){
            return $e->getMessage();
        }
    }

    public function getPersonnelByOffice($id, $withHeader=0, $update=0)
    {
        try {

            $personnel = array();

            $response = HTTP::post('https://hris.usep.edu.ph/hris/api/epms/employee/department', [
                "department_id" => (int)$id,
                "token" => env("DATA_HRIS_API_TOKEN")
            ]);

            $lists = json_decode($response->body());

            $obj = new \stdClass();

            if($withHeader) {
                $obj->id = "all";
                $obj->value = "all";
                $obj->title = "All Personnel";
                $obj->isPersonnel = 1;
                $obj->children = [];

                array_push($personnel, $obj);
            }

            if(count($lists)){
                foreach ($lists as $list){
                    $lastName = mb_strtolower($list->LastName);
                    $firstName = mb_strtolower($list->FirstName);
                    $middleName = mb_strtolower($list->MiddleName);

                    $fullName = $firstName . " " . substr($middleName,0,1) . ". " . $lastName;

                    $obj = new \stdClass();

                    $obj->id = $list->PmapsID;
                    $obj->value = $list->PmapsID;
                    $obj->title = ucwords($fullName);
                    $obj->isPersonnel = 1;

                    if($withHeader) {
                        array_push($personnel[0]->children, $obj);
                    }else {
                        array_push($personnel, $obj);
                    }
                }
            }

            if(!$update){
                return response()->json([
                    'personnel' => $personnel
                ], 200);
            }else{
                return $personnel;
            }
        }catch(\Exception $e){
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function getAllPositions()
    {
        $response = HTTP::post('https://hris.usep.edu.ph/hris/api/epms/employee/position/list', [
            "token" => env("DATA_HRIS_API_TOKEN")
        ]);

        $data = json_decode($response->body());

        $positionList = [];

        foreach($data as $list) {
            if(!in_array($list->Name, $positionList)) {
                array_push($positionList, $list->Name);
            }
        }

        return response()->json([
            'positionList' => $positionList
        ], 200);
    }
}
