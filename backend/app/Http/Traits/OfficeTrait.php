<?php

namespace App\Http\Traits;

use App\Group;
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
                'token' => config('services.hris.data')
            ]);

            $vpoffices = json_decode($response->body());

            if(count($vpoffices)) {
                foreach ($vpoffices as $vpoffice) {

                    $data = new \stdClass();

//                    $data->id = $vpoffice->id;
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

            $isAcronym = isset($status->isAcronym) && $status->isAcronym;

            $response = HTTP::post('https://hris.usep.edu.ph/hris/api/epms/department', [
                'token' => config('services.hris.data')
            ]);

            $vpoffices = json_decode($response->body());

            if(count($vpoffices)) {
                foreach ($vpoffices as $vpoffice) {

                    $data = new \stdClass();

                    $data->id = $vpoffice->id;
                    $data->value = $vpoffice->id;
                    $data->title = $isAcronym ? $vpoffice->Acronym : $vpoffice->Department;
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

    public function getOfficesAccountable($nodeStatus)
    {
        $offices = $this->getMainOfficesWithChildren($nodeStatus, ['fromForm' => true]);

        $decoded = json_decode($nodeStatus);

        $groups = Group::where('effective_until', '>=', $decoded->currentYear)->get();

        $children = [];

        foreach($groups as $group) {
            $data = new \stdClass();

            $data->id = $group->id;
            $data->value = $group->id;
            $data->title = $group->name;
            $data->pId = 'allGroups';
            $data->cascadeTo = null;
            $data->isGroup = true;

            array_push($children, $data);
        }

        $data = new \stdClass();

        $data->id = "groups";
        $data->value = "groups";
        $data->title = "Groups";
        $data->isGroup = true;
        $data->cascadeTo = null;
        $data->children = $children;
        $data->checkable = false;
        $data->selectable = false;

        array_push($offices, $data);

        return response()->json([
            'officesAccountable' => $offices
        ], 200);
    }

    public function getChildOffices($vp_id, $update=0)
    {
        try {
            if($vp_id === 'allColleges'){
                $response = HTTP::post('https://hris.usep.edu.ph/hris/api/epms/college', [
                    'token' => config('services.hris.data')
                ]);
            }else{
                $response = HTTP::post('https://hris.usep.edu.ph/hris/api/epms/structure', [
                    "department_id" => (int)$vp_id,
                    "token" => config('services.hris.data')
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
                "token" => config('services.hris.data')
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
                    $obj->position = $list->Position;
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
            "token" => config('services.hris.data')
        ]);

        $data = json_decode($response->body());

        $positionList = [];

        foreach($data as $key => $list) {
            if(!$this->checkArrayObjectsValue($positionList, 'value', $list->Name)) {
                $obj = new \stdClass();

                $obj->value = $list->Name;
                $obj->key = $key;

                array_push($positionList, $obj);
            }
        }

        return response()->json([
            'positionList' => $positionList
        ], 200);
    }

    public function getUserOffices($form)
    {
        try{
            $response = HTTP::post('https://hris.usep.edu.ph/hris/api/epms/employee/pmaps', [
                "pmaps_id" => $this->login_user->pmaps_id,
                "token" => config('services.hris.data')
            ]);

            $data = json_decode($response->body());

            $personnelOffices = array();

            if(count($data)){
                foreach($data as $datum) {
                    $isCollege = filter_var($datum->isCollege, FILTER_VALIDATE_BOOLEAN);

                    $add = ($form === 'cpcr' && !$isCollege) || ($form === 'opcr' && $isCollege) ? false : true;

                    if($add) {
                        $ifExists = $this->array_any(function($x, $compare){
                            return $x->id === $compare['id'];
                        }, $personnelOffices, ['id' => $datum->DepartmentID]);

                        if(!$ifExists){
                            $newOffice = new \stdClass();

                            $newOffice->key = $datum->DepartmentID;
                            $newOffice->value = $datum->DepartmentID;
                            $newOffice->title = $datum->DepartmentName;
                            $newOffice->acronym = $datum->Acronym;

                            array_push($personnelOffices, $newOffice);
                        }
                    }
                }
            }

            return response()->json([
                'personnelOffices' => $personnelOffices
            ], 200);
        }catch(\Exception $e){
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }

    }

    public function getOfficeParentId($officeId, $form)
    {
        $vpOfficeId = 'colleges';

        if($form === 'opcr'){
            $params = array(
                "token" => config('services.hris.data'),
                "department_id" => $officeId
            );

            $response = Http::post('https://hris.usep.edu.ph/hris/api/epms/structure/subdepartment', $params);

            $objs = json_decode($response->body());

            if(count($objs)) {
                foreach ($objs as $obj) {
                    $vpOfficeId = $obj->HeadDepartmentID;
                }
            }
        }

        return $vpOfficeId;
    }

    public function getVpOfficeWithChildren(){

        $response = HTTP::post('https://hris.usep.edu.ph/hris/api/epms/department', [
            'token' => config('services.hris.data')
        ]);

        $vpOffices = json_decode($response->body());

        if ($vpOffices){
            foreach ($vpOffices as $key => $value){
                $isUpdate = 1;
                $vpOffices[$key]->value = $value->id;
                $vpOffices[$key]->title = $value->Department;
                $vpOffices[$key]->selectable = false;
                $vpOffices[$key]->children = $this->getChildOffices($vpOffices[$key]->value, $isUpdate);
            }
        }
        return response()->json([
            'vpOffices' => $vpOffices
        ], 200);
    }

}
