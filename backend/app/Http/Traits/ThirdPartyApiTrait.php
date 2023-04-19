<?php

namespace App\Http\Traits;

use Illuminate\Support\Facades\Http;

trait ThirdPartyApiTrait {

    protected $hris = [
        // Get all parent offices; params: token
        'ALL_PARENT_OFFICES' => 'https://hris.usep.edu.ph/hris/api/epms/department',
        // Get all colleges; params: token
        'ALL_COLLEGES' => 'https://hris.usep.edu.ph/hris/api/epms/college',
        // Get all subunit by office id; params: token, department_id
        'OFFICE_SUBUNIT' => 'https://hris.usep.edu.ph/hris/api/epms/structure/subunit',
        // Get all sub departments by parent ID; params: department_id, token
        'OFFICES_BY_PARENT' => 'https://hris.usep.edu.ph/hris/api/epms/structure',
        // Get all employee by office; params: department_id, token
        'EMPLOYEES_BY_OFFICES' => 'https://hris.usep.edu.ph/hris/api/epms/employee/department',
        // Get all employee positions; params: token
        'ALL_EMPLOYEE_POSITIONS' => 'https://hris.usep.edu.ph/hris/api/epms/employee/position/list',
        // Get the parent details of sub department; params: department_id, token
        'GET_SUB_DEPARTMENT_PARENT' => 'https://hris.usep.edu.ph/hris/api/epms/structure/subdepartment',
    ];

    public function HRIS_CALL($id, $params=[])
    {
        try{
            $params['token'] = config('services.hris.data');

            $url = $this->hris[$id];

            $response = HTTP::post($url, $params);

            return json_decode($response->body());
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
