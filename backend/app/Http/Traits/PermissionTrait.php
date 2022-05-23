<?php

namespace App\Http\Traits;

use App\AccessRight;
use App\UserAccessRights;
use Illuminate\Support\Facades\Auth;

trait PermissionTrait {

    private function fetchPermissions(){
        $this->permissions = AccessRight::get();
    }

    private function fetchUserAccessRights(){
        $user = Auth::user();
        $this->userAccessRights = UserAccessRights::where('user_id', $user->id)->where('deleted_at', null)->get();
    }

    private function fetchAllChildrenPermission($id){
        $permissions = array();
        foreach ($this->getChildrenPermission($id) as $key => $value){
            $permissions = array_merge($permissions,$this->fetchAllChildrenPermission($value));
        }
        return array_merge($this->getChildrenPermission($id),$permissions);
    }

    private function getChildrenPermission($id){
        $permissions = array();
        foreach ($this->permissions as $key => $value){
            if ($value->parent_id === $id) array_push($permissions,$value->id);
        }
        return $permissions;
    }

    private function getParentPermission($id){
        foreach ($this->permissions as $key => $value){
            if ($value->id === $id) return $value->parent_id;
        }
        return false;
    }

    private function hasChildrenPermission($permissions){
        foreach ($this->userAccessRights as $accessKey => $accessValue){
            foreach ($permissions as $permissionKey => $permissionValue){
                if ($accessValue->access_right_id == $permissionValue){
                    return true;
                }
            }
        }
        return false;
    }

    private function hasUserAccess($id){
        foreach ($this->userAccessRights as $key => $value){
            if ($id == $value->access_right_id) return true;
        }
        return false;
    }

    private function getIdByPermissionId($permission_id){
        foreach ($this->permissions as $key => $value){
            if ($permission_id == $value->permission_id) return $value->id;
        }
        return false;
    }

}
