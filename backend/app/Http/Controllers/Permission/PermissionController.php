<?php

namespace App\Http\Controllers\Permission;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\AccessRight;
use App\User;
use App\UserAccessRights;
use App\Http\Traits\OfficeTrait;
use App\Http\Traits\FormTrait;
use App\Http\Requests\StoreUserPermission;
use Illuminate\Support\Facades\DB;


class PermissionController extends Controller
{
    use OfficeTrait,FormTrait;

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
            $personnelId = (int)$request->personnelId;
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
                $accessLists  = UserAccessRights::where([
                                                        ['user_id', $details->UserID],
                                                        ])->get();

                                                        // dd($accessList.value);
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
                    array_push($currentAccessList,$permission);
                }

            return response()->json("Access rights have been save!", 200);
           
        }catch(\Exception $e){
            return response()->json($e->getMessage());
        }
        
    }

    function fetchPermissionByUser($id, $withHeader=0, $update=0)
    {   
        try{
            $lists = [];
            $userInfo  = User::where([
                ['pmaps_id', $id],
                ])->first();

            $accessLists  = UserAccessRights::where([
                ['user_id',$userInfo['id']],
                ])->get();
           
            if(count($accessLists)>0){
                    $status = true;
            }else{
                $status = false;
            }

            return response()->json([
                    'accessLists' => isset($accessLists) ? $accessLists : "",
                    'status'=>$status
                ], 200);
        }catch(\Exception $e){
            return response()->json($e->getMessage());
        }
      
        
    }

    function updatePermission(StoreUserPermission $request)
    {
        try{
            $personnelId = (int)$request->personnelId;
            $listPermissions = $request->listPermissions;
            $user = User::find($personnelId);
            // dd($user);

            $response = Http::post('https://hris.usep.edu.ph/hris/api/epms/employee/pmaps', [
                "token" => config('services.hris.data'),
                "pmaps_id" => $personnelId,
            ]);
           
            $obj = json_decode($response->body());
            $details = $obj[0];


            $accessLists  = UserAccessRights::where([
                                    ['user_id', $details->UserID],
                                    ])->get();
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
                    array_push($accessAllListsExist,$permission);
                   
                   

                }
                array_push($currentList,$permission);
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
            
            return response()->json("Access rights have been save!", 200);
           
        }catch(\Exception $e){
            return response()->json($e->getMessage());
        }
        
    }
    

  

}
