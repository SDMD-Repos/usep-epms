<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccessRightsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('access_rights')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        DB::table('access_rights')->insert([
            [
                'permission_id' => 'manager',
                'permission_name' => 'Manager',
                'create_id' => 'admin',
            ],
        ]);

        DB::table('access_rights')->insert([
            [
                'permission_id' => 'm-form',
                'permission_name' => 'Form',
                'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","manager")->first()->id,
                'create_id' => 'admin',
            ],
            [
                'permission_id' => 'm-group',
                'permission_name' => 'Groups',
                'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","manager")->first()->id,
                'create_id' => 'admin',
            ],
            [
                'permission_id' => 'm-measures',
                'permission_name' => 'Measures',
                'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","manager")->first()->id,
                'create_id' => 'admin',
            ],
        ]);


        DB::table('access_rights')->insert([
            [
                'permission_id' => 'mf-functions',
                'permission_name' => 'Functions',
                'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","m-form")->first()->id,
                'create_id' => 'admin',
            ],
            [
                'permission_id' => 'mf-programs',
                'permission_name' => 'Programs',
                'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","m-form")->first()->id,
                'create_id' => 'admin',
            ],
            [
                'permission_id' => 'mf-subcat',
                'permission_name' => 'Sub Categories',
                'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","m-form")->first()->id,
                'create_id' => 'admin',
            ],
            [
                'permission_id' => 'mf-fields',
                'permission_name' => 'Fields',
                'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","m-form")->first()->id,
                'create_id' => 'admin',
            ],
        ]);


        DB::table('access_rights')->insert([
            [
                'permission_id' => 'mff-create',
                'permission_name' => 'create',
                'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","mf-functions")->first()->id,
                'create_id' => 'admin',
            ],
            [
                'permission_id' => 'mff-delete',
                'permission_name' => 'delete',
                'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","mf-functions")->first()->id,
                'create_id' => 'admin',
            ],
            [
                'permission_name' => 'create',
                'permission_id' => 'mg-create',
                'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","m-group")->first()->id,
                'create_id' => 'admin',
            ],
            [
                'permission_id' => 'mg-delete',
                'permission_name' => 'delete',
                'create_id' => 'admin',
                'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","m-group")->first()->id,
            ],
            [
                'permission_id' => 'mg-edit',
                'permission_name' => 'edit',
                'create_id' => 'admin',
                'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","m-group")->first()->id,
            ],
            [
                'permission_id' => 'mm-create',
                'permission_name' => 'create',
                'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","m-measures")->first()->id,
                'create_id' => 'admin',
            ],
            [
                'permission_id' => 'mm-delete',
                'permission_name' => 'delete',
                'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","m-measures")->first()->id,
                'create_id' => 'admin',
            ],
            [
                'permission_id' => 'mm-edit',
                'permission_name' => 'edit',
                'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","m-measures")->first()->id,
                'create_id' => 'admin',
            ],
        ]);

        DB::table('access_rights')->insert([
            [
                'permission_id' => 'mfp-create',
                'permission_name' => 'create',
                'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","mf-programs")->first()->id,
                'create_id' => 'admin',
            ],
            [
                'permission_id' => 'mfp-delete',
                'permission_name' => 'delete',
                'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","mf-programs")->first()->id,
                'create_id' => 'admin',
            ],
        ]);

        DB::table('access_rights')->insert([
            [
                'permission_id' => 'mfs-create',
                'permission_name' => 'create',
                'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","mf-subcat")->first()->id,
                'create_id' => 'admin',
            ],
            [
                'permission_id' => 'mfs-delete',
                'permission_name' => 'delete',
                'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","mf-subcat")->first()->id,
                'create_id' => 'admin',
            ],

        ]);


        DB::table('access_rights')->insert([
            [
                'permission_id' => 'adminPermission',
                'permission_name' => 'Access Rights',
                'create_id' => 'admin',
            ],
        ]);

        DB::table('access_rights')->insert([
            [
                'permission_id' => 'form',
                'permission_name' => 'Forms',
                'create_id' => 'admin',
            ],
        ]);

        DB::table('access_rights')->insert([
            [
                'permission_id' => 'f-aapcr',
                'permission_name' => 'Access Form and List modules in AAPCR',
                'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","form")->first()->id,
                'create_id' => 'admin',
            ],
            [
                'permission_id' => 'f-opcrvp',
                'permission_name' => 'Access Form and List modules in OPCR (VP)',
                'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","form")->first()->id,
                'create_id' => 'admin',
            ],
            [
                'permission_id' => 'f-opcr',
                'permission_name' => 'OPCR',
                'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","form")->first()->id,
                'create_id' => 'admin',
            ],
            [
                'permission_id' => 'f-cpcr',
                'permission_name' => 'Access Form and List modules in CPCR',
                'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","form")->first()->id,
                'create_id' => 'admin',
            ],
            [
                'permission_id' => 'f-ipcr',
                'permission_name' => 'Access Form and List modules in IPCR',
                'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","form")->first()->id,
                'create_id' => 'admin',
            ],
        ]);

        DB::table('access_rights')->insert([
            [
                'permission_id' => 'fo-formlist',
                'permission_name' => 'Access Form and List modules',
                'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","f-opcr")->first()->id,
                'create_id' => 'admin',
            ],
            [
                'permission_id' => 'fo-manager',
                'permission_name' => 'Access Manager module',
                'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","f-opcr")->first()->id,
                'create_id' => 'admin',
            ],
            [
                'permission_id' => 'fo-template',
                'permission_name' => 'Access Template Form and List modules',
                'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","f-opcr")->first()->id,
                'create_id' => 'admin',
            ],
        ]);


        DB::table('access_rights')->insert([
            [
                'permission_id' => 'adminRequests',
                'permission_name' => 'Requests',
                'create_id' => 'admin',
            ],
        ]);

        DB::table('access_rights')->insert([
            [
                'permission_id' => 'ap-manager',
                'permission_name' => 'Manager',
                'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","adminPermission")->first()->id,
                'create_id' => 'admin',
            ],
            [
                'permission_id' => 'ap-form',
                'permission_name' => 'Form',
                'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","adminPermission")->first()->id,
                'create_id' => 'admin',
            ],

        ]);

        // Requests
        DB::table('access_rights')->insert([
        [
            'permission_id' => 'r-main-forms',
            'permission_name' => 'Main Forms',
            'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","adminRequests")->first()->id,
            'create_id' => 'admin',
        ],
        [
            'permission_id' => 'r-ipcr',
            'permission_name' => 'IPCR',
            'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","adminRequests")->first()->id,
            'create_id' => 'admin',
        ]
    ]);

        DB::table('access_rights')->insert([
            [
                'permission_id' => 'apf-aapcr',
                'permission_name' => 'Set assigned office head to AAPCR',
                'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","ap-form")->first()->id,
                'create_id' => 'admin',
            ],
            [
                'permission_id' => 'apf-opcrvr',
                'permission_name' => 'Set assigned office head to each OPCR (VP)',
                'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","ap-form")->first()->id,
                'create_id' => 'admin',
            ],
            [
                'permission_id' => 'apf-opcr',
                'permission_name' => 'Set assigned office head to each OPCR',
                'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","ap-form")->first()->id,
                'create_id' => 'admin',
            ],
            [
                'permission_id' => 'apf-cpcr',
                'permission_name' => 'Set assigned office head to each CPCR',
                'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","ap-form")->first()->id,
                'create_id' => 'admin',
            ],

        ]);
    }
}
