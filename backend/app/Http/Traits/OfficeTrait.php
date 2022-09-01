<?php

namespace App\Http\Traits;

use App\Group;
use Illuminate\Http\Request;

trait OfficeTrait {
    use ThirdPartyApiTrait;

    public function getMainOfficesOnly($officesOnly=0, $returnJson=1 )
    {
        try {
            $values = array();

            if(!$officesOnly) {
                $data = new \stdClass();

                $data->id = "allColleges";
                $data->value = "allColleges";
                $data->title = "All Colleges";

                $values[] = $data;
            }

            $vpOffices = $this->HRIS_CALL('ALL_PARENT_OFFICES');

            if(count($vpOffices)) {
                foreach ($vpOffices as $vpOffice) {

                    $data = new \stdClass();

                    $data->value = $vpOffice->id;
                    $data->title = $vpOffice->Department;

                    $values[] = $data;
                }

                if($returnJson){
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

    public function getMainOfficesWithChildren($status, $params=array())
    {
        $status = count($status) ? $status : request()->all();

        $checkable = null;
        $selectable = null;

        if(isset($status['checkable'])) {
            $checkable = $status['checkable'];
        } elseif(isset($status['selectable'])) {
            $selectable = $status['selectable'];
        }

        try {
            $values = array();

            $data = new \stdClass();

            if(!isset($status['isOfficesOnly']) || (!$status['isOfficesOnly'])) {
                $data->id = "allColleges";
                $data->value = "allColleges";
                $data->title = "All Colleges";
                $data->cascadeTo = null;
                $data->children = $this->getChildOffices("allColleges",1);

                if($checkable) {
                    $data->checkable = $checkable['allColleges'];
                } elseif($selectable) {
                    $data->selectable = $selectable['allColleges'];
                }

                $values[] = $data;
            }

            $isAcronym = isset($status['isAcronym']) && $status['isAcronym'];

            $vpOffices = $this->HRIS_CALL('ALL_PARENT_OFFICES');

            if(count($vpOffices)) {
                foreach ($vpOffices as $vpOffice) {

                    $data = new \stdClass();

                    $data->id = $vpOffice->id;
                    $data->value = $vpOffice->id;
                    $data->title = $isAcronym ? $vpOffice->Acronym : $vpOffice->Department;
                    $data->cascadeTo = null;
                    $data->children = $this->getChildOffices($vpOffice->id,1);

                    if($checkable) {
                        $data->checkable = $checkable['mains'];
                    } elseif($selectable) {
                        $data->selectable = $selectable['mains'];
                    }

                    $values[] = $data;
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

    public function getOfficesAccountable(Request $request)
    {
        $nodeStatus = $request->input();

        $groupStatus = $nodeStatus['groups'] ?? null;

        $offices = $this->getMainOfficesWithChildren($nodeStatus, ['origin' => 'form']);

        if($groupStatus && $groupStatus['included']) {
            $groups = Group::where('effective_until', '>=', $nodeStatus['currentYear'])->get();

            $children = [];

            foreach($groups as $group) {
                $data = new \stdClass();

                $data->id = $group->id;
                $data->value = $group->id;
                $data->title = $group->name;
                $data->pId = 'allGroups';
                $data->cascadeTo = null;
                $data->isGroup = true;
                $data->disabled = isset($groupStatus['officeId']) && ($groupStatus['officeId']['key'] !== $group->supervising_id);

                $children[] = $data;
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

            $offices[] = $data;
        }

        return response()->json([
            'officesAccountable' => $offices
        ], 200);
    }

    public function getChildOffices($vp_id, $update=0)
    {
        try {
            if($vp_id === 'allColleges'){
                $obj = $this->HRIS_CALL('ALL_COLLEGES');
            }else{
                $obj = $this->HRIS_CALL('OFFICES_BY_PARENT', ['department_id' => $vp_id ]);
            }

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

                    $values[] = $data;
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

    public function getPersonnelByOffice($id, $permanentOnly=0, $withHeader=0, $returnJson=1)
    {
        try {
            $personnel = array();

            $lists = $this->HRIS_CALL('EMPLOYEES_BY_OFFICES', ['department_id' => $id ]);

            if($permanentOnly) {
                $lists = array_filter($lists, function($x) {
                    return $x->isPermanent === true;
                });
            }

            $obj = new \stdClass();

            if($withHeader) {
                $obj->id = "all";
                $obj->value = "all";
                $obj->title = "All Personnel";
                $obj->isPersonnel = 1;
                $obj->isPermanent = false;
                $obj->children = [];

                $personnel[] = $obj;
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
                    $obj->isPermanent = $list->isPermanent;
                    $obj->isPersonnel = 1;

                    if($withHeader) {
                        $personnel[0]->children[] = $obj;
                    }else {
                        $personnel[] = $obj;
                    }
                }
            }

            if($returnJson){
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
        $data = $this->HRIS_CALL('ALL_EMPLOYEE_POSITIONS');

        $positionList = [];

        foreach($data as $key => $list) {
            if(!$this->checkArrayObjectsValue($positionList, 'value', $list->Name)) {
                $obj = new \stdClass();

                $obj->value = $list->Name;
                $obj->key = $key;

                $positionList[] = $obj;
            }
        }

        return response()->json([
            'positionList' => $positionList
        ], 200);
    }

    public function checkArrayObjectsValue($lists, $object, $value)
    {
        $found = false;

        foreach ($lists as $list) {
            if ($list->$object === $value) { $found = true; }
        }

        return $found;
    }

    public function processVpOffices($list, $origin)
    {
        $offices = array();

        $officeList = $list->offices;

        foreach($officeList as $datum) {
            $officeType = $datum->field->code;

            $counter = isset($offices[$officeType]) ? count($offices[$officeType]) : 0;

            $officeId = is_numeric($datum->office_id) ? (int)$datum->office_id : $datum->office_id;
            $officeName = $datum->office_name;

            if ($datum->is_group) {
                $officeId = $datum->group->id;
                $officeName = $datum->group->name;
            }

            switch ($origin) {
                case 'vpopcr-view':
                    $cascadeTo = $datum->category_id;

                    if($datum->program_id) {
                        $cascadeTo = $datum->program->category_id . '-' . $datum->program_id;
                    }else if($datum->other_program_id) {
                        $cascadeTo = $datum->otherProgram->category_id . '-' . $datum->other_program_id . '-opcr';
                    }
                    break;
                default:
                    $cascadeTo = $datum->cascade_to;
                    break;
            }

            if(!$datum->vp_office_id && !$datum->is_group) {
                $children = $this->HRIS_CALL('OFFICES_BY_PARENT', ['department_id' => $officeId]);

                foreach ($children as $child) {
                    $offices[$officeType][$counter] = array(
                        'title' => $child->Acronym,
                        'value' => $child->id,
                        'cascadeTo' => $cascadeTo,
                        'pId' => $officeId,
                        'acronym' => $child->Acronym,
                    );
                    $counter++;
                }
            }else {
                $offices[$officeType][$counter] = array(
                    'title' => $officeName,
                    'value' => $officeId,
                    'cascadeTo' => $cascadeTo,
                );
            }

            if ($datum->is_group) {
                $offices[$officeType][$counter]['isGroup'] = true;
            }

            if($datum->vp_office_id){
                $offices[$officeType][$counter]['pId'] = $datum->vp_office_id;

                $offices[$officeType][$counter]['acronym'] = $datum->office_name; # used for view only PIs
            }/*else{
                if (!$datum->is_group && !$datum->vp_office_id) {
                    $offices[$officeType][$counter]['children'] = true;
                }
            }*/
        }

        return $offices;
    }

    /*public function getUserOffices($form)
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

                    $add = !(($form === 'cpcr' && !$isCollege) || ($form === 'opcr' && $isCollege));

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

                            $personnelOffices[] = $newOffice;
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

    }*/

    public function getOfficeParentId($officeId, $form)
    {
        $vpOfficeId = 'colleges';

        if($form === 'opcr'){
            $objs = $this->HRIS_CALL('GET_SUB_DEPARTMENT_PARENT', ['department_id' => $officeId ]);

            if(count($objs)) {
                foreach ($objs as $obj) {
                    $vpOfficeId = $obj->HeadDepartmentID;
                }
            }
        }

        return $vpOfficeId;
    }

}
