<?php

namespace App\Http\Controllers\SystemAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\AccessRight;
use App\User;
use App\UserAccessRights;
use App\Http\Traits\OfficeTrait;
use App\Http\Traits\PermissionTrait;
use App\Http\Traits\FormTrait;
use App\Http\Requests\StoreUserPermission;
use App\FormAccess;
use App\Http\Requests\StoreFormAccess;

class PermissionController extends Controller
{
    use OfficeTrait, FormTrait, PermissionTrait;

    private $userAccessRights;

    private $opcrFormId = 'opcr';
    private $opcrvpFormId = 'vpopcr';

    function detailsPermission()
    {
        $accessPermission = AccessRight::orderBy('id', 'ASC')->get();

        $modSubCategories = $this->getNestedChildren($accessPermission);

        return response()->json([
            'accessPermission' => $modSubCategories
        ], 200);
    }

    function savePermission(StoreUserPermission $request)
    {
        try{
            $personnelId = $request->personnelId;

            $listPermissions = $request->listPermissions;

            $user = User::find($personnelId);

            $response = Http::post('https://hris.usep.edu.ph/hris/api/epms/employee/pmaps', [
                "token" => config('services.hris.data'),
                "pmaps_id" => $personnelId,
            ]);

            $obj = json_decode($response->body());
            $details = $obj[0];

            if(!$user){
                if (isset($obj[0]->PmapsID)) {
                    $user = User::updateOrCreate(
                        ['id' => $details->UserID ],
                        [
                            'id' => $details->UserID,
                            'pmaps_id' => $details->PmapsID,
                            'firstName' => $details->FirstName,
                            'middleName' => $details->MiddleName,
                            'lastName' => $details->LastName,
                            'email' => "",
                        ]
                    );
                }else {
                    return response()->json("PMAPS was not registered in HRIS", 400);
                }
            }

            $accessLists  = UserAccessRights::where('user_id', $details->UserID)->get();

            $accessAllLists = [];

            foreach ($accessLists as $list){
                array_push($accessAllLists,$list['access_right_id']);
            }

            $currentAccessList = [];

            foreach ($listPermissions as $permission) {
              $userAccessRights = new UserAccessRights();
              $accessList  = UserAccessRights::where([
                                ['user_id', $details->UserID],
                                ['access_right_id', $permission],
                            ])->get();

                if(!count($accessList)){
                    $userAccessRights->user_id = (string)$details->UserID;
                    $userAccessRights->access_right_id = $permission;
                    $userAccessRights->create_id = $this->login_user->pmaps_id;
                    $userAccessRights->save();
                }

                array_push($currentAccessList, $permission);
            }

            return response()->json("Access rights have been save!", 200);

        }catch(\Exception $e){
            return response()->json($e->getMessage());
        }
    }

    function fetchPermissionByUser($id, $withHeader=0, $update=0)
    {
        try{
            $userInfo  = User::where('pmaps_id', $id)->first();

            $accessLists  = UserAccessRights::where('user_id',$userInfo['id'])->get();

            $status = (bool)count($accessLists);

            return response()->json([
                'accessLists' => $accessLists ?? "",
                'status'=> $status
            ], 200);
        }catch(\Exception $e){
            return response()->json($e->getMessage());
        }
    }

    function updatePermission(StoreUserPermission $request)
    {
        try{
            $personnelId = $request->personnelId;

            $listPermissions = $request->listPermissions;

            $response = Http::post('https://hris.usep.edu.ph/hris/api/epms/employee/pmaps', [
                "token" => config('services.hris.data'),
                "pmaps_id" => $personnelId,
            ]);

            $obj = json_decode($response->body());
            $details = $obj[0];

            $accessLists  = UserAccessRights::where('user_id', $details->UserID)->get();

            $accessAllListsExist = [];

            $currentList = [];

            foreach ($accessLists as $list){
                array_push($accessAllListsExist,$list['access_right_id']);
            }

            foreach($listPermissions as $permission){
                if(!in_array($permission,$accessAllListsExist)){
                    $userAccessRights = new UserAccessRights();

                    $userAccessRights->user_id = (string)$details->UserID;
                    $userAccessRights->access_right_id = $permission;
                    $userAccessRights->create_id = $this->login_user->pmaps_id;

                    $userAccessRights->save();

                    array_push($accessAllListsExist, $permission);
                }

                array_push($currentList, $permission);
            }

            $removePermissionList= array_diff($accessAllListsExist,$currentList);

            foreach($removePermissionList as $removeList){
                $accessLists  = UserAccessRights::where([
                    ['user_id', $details->UserID],
                    ['access_right_id', $removeList]
                ])->get();

                foreach($accessLists as $newList){
                    $newList->delete();
                }
            }
            if(!count($currentList)){
                $accessListss  = UserAccessRights::where([
                    ['user_id', $details->UserID],
                ])->get();
                foreach($accessListss as $newList){
                    $newList->delete();
                }
            }

            return response()->json("Access rights have been save!", 200);

        }catch(\Exception $e){
            return response()->json($e->getMessage());
        }
    }

    function saveOfficeHead(StoreFormAccess $request){

        try {
            $validated = $request->validated();
            $pmaps_id = $validated['pmaps_id']['value'];
            $pmaps_name = $validated['pmaps_id']['label'];
            $office_name = $validated['office_id']['label'];
            $office_id = $validated['office_id']['value'];
            $form_id = $validated['form_id'];
            $condition = ['form_id' => $form_id];

            if ($this->opcrFormId === $form_id || $this->opcrvpFormId === $form_id){
                $condition['office_id'] = $office_id;
            }

            $user = FormAccess::updateOrCreate(
                $condition,
                [
                    'form_id' => $form_id,
                    'pmaps_id' => $pmaps_id,
                    'pmaps_name' => $pmaps_name,
                    'office_id'=>$office_id,
                    'office_name'=>$office_name,
                    'staff_id' => '',
                    'staff_name' => '',
                    'create_id'=> $this->login_user->pmaps_id,

                ]
            );


            return response()->json("Office head has been assigned!", 200);
        } catch (\Exception $e) {
            return response()->json($e->getMessage());
        }
    }

    public function checkAccessByPermissions(Request $request)
    {
        $this->fetchPermissions();
        $this->fetchUserAccessRights();

        if (!!$request->input()){
            foreach ($request->input() as $key => $value) {
                $module = $this->getIdByPermissionId($value);
                $cPermission = $this->fetchAllChildrenPermission($module);
                if ($this->hasUserAccess($module) && !$this->hasChildrenPermission($cPermission)) {
                    return response()->json(['hasPermission' => true], 200);
                }else{
                    $parent = $this->getParentPermission($module);
                    do {
                        $pPermission = $this->fetchAllChildrenPermission($parent);
                        if ($this->hasUserAccess($parent) && !$this->hasChildrenPermission($pPermission)){

                            return response()->json(['hasPermission' => true], 200);
                        }
                    } while ($parent = $this->getParentPermission($parent));
                }
            }
        }

        return response()->json(['hasPermission' => false], 200);
    }

    function fetchOfficeHead($form_id,$office_id=""){

        try{

            $condition  = ['form_id' => $form_id];
            if($form_id==='vpopcr'|| $form_id==='opcr' ){
                $condition['office_id'] = $office_id;
            }

            $officeHeadDetails  = FormAccess::where($condition)->first();

            return response()->json([
                'officeHeadDetails' => $officeHeadDetails,

            ], 200);
        }catch(\Exception $e){
            return response()->json($e->getMessage());
        }
    }

    function saveOfficeStaff(StoreFormAccess $request){

        try {
            $validated = $request->validated();
            $pmaps_id = $validated['pmaps_id']['value'];
            $pmaps_name = $validated['pmaps_id']['label'];
            $office_id = $validated['office_id']['value'];
            $form_id = $validated['form_id'];
            $condition = ['form_id' => $form_id];

            if ($this->opcrFormId === $form_id || $this->opcrvpFormId === $form_id ){
                $condition['office_id'] = $office_id;
            }

            $user = FormAccess::updateOrCreate(
                $condition,
                [
                    'form_id' => $form_id,
                    'staff_id' => $pmaps_id,
                    'staff_name' => $pmaps_name,
                    'create_id'=> $this->login_user->pmaps_id,

                ]
            );

            return response()->json("Office Staff has been assigned!", 200);
        } catch (\Exception $e) {
            return response()->json($e->getMessage());
        }
    }

    function getFormAccessByOffice($office_id){

        try{
            $formAccessDetails  = FormAccess::where('office_id',$office_id)->first();
            return response()->json([
                'formAccessDetails' => $formAccessDetails,

            ], 200);
        }catch(\Exception $e){
            return response()->json($e->getMessage());
        }
    }
    function checkFormHead($pmaps_id,$form_id){

        try{
            $permission = false;
            // $formAccessDetails  = FormAccess::where('office_id',$office_id)->first();
            $formAccessDetails  = FormAccess::where([['pmaps_id',$pmaps_id],['form_id',$form_id]])->get();

            if(count($formAccessDetails)){
              $permission = true;
            }
            return response()->json([
                'permission' => $permission,
            ], 200);
        }catch(\Exception $e){
            return response()->json($e->getMessage());
        }
    }
    function allowForm($pmaps_id,$form_id){

        try{
            $permission = false;
            $office_id = 0;
            $formAccessDetails  = FormAccess::where([['pmaps_id',$pmaps_id],['form_id',$form_id]])->get();

            $formStaffAccessDetails  = FormAccess::where([['staff_id',$pmaps_id],['form_id',$form_id]])->get();

            if(count($formAccessDetails)){
                $permission = true;
                $office_id = $formAccessDetails[0]->office_id;
            }

            if(count($formStaffAccessDetails)){
                $permission = true;
                $office_id = $formStaffAccessDetails[0]->office_id;
            }

            return response()->json([
                'permission' => $permission,
                'office_id'=> $office_id,

            ], 200);
        }catch(\Exception $e){
            return response()->json($e->getMessage());
        }
    }

}
