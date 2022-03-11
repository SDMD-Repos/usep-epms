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
            [
                'permission_id' => 'adminPermission',
                'permission_name' => 'Access Permission',
                'create_id' => 'admin',
            ],
            [
                'permission_id' => 'adminRequests',
                'permission_name' => 'Requests',
                'create_id' => 'admin',
            ],
        ]);

        DB::table('access_rights')->insert([
            [
                'permission_id' => 'm-group',
                'permission_name' => 'Group',
                'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","manager")->first()->id,
                'create_id' => 'admin',
            ],
            [
                'permission_id' => 'm-measures',
                'permission_name' => 'Measures',
                'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","manager")->first()->id,
                'create_id' => 'admin',
            ],
            // [
            //     'permission_id' => 'm-signatories',
            //     'permission_name' => 'Signatories',
            //     'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","manager")->first()->id,
            //     'create_id' => 'admin',
            // ],
            [
                'permission_id' => 'm-form',
                'permission_name' => 'Form',
                'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","manager")->first()->id,
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

        // DB::table('access_rights')->insert([
        //     [
        //         'permission_id' => 'ms-aapcr',
        //         'permission_name' => 'AAPCR Signatory',
        //         'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","m-signatories")->first()->id,
        //         'create_id' => 'admin',
        //     ],
        //     [
        //         'permission_id' => 'ms-opcrvp',
        //         'permission_name' => 'OPCR (VPs) Signatory',
        //         'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","m-signatories")->first()->id,
        //         'create_id' => 'admin',
        //     ],
        //     [
        //         'permission_id' => 'ms-opcr',
        //         'permission_name' => 'OPCR Signatory',
        //         'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","m-signatories")->first()->id,
        //         'create_id' => 'admin',
        //     ],
        //     [
        //         'permission_id' => 'ms-cpcr',
        //         'permission_name' => 'CPCR Signatory',
        //         'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","m-signatories")->first()->id,
        //         'create_id' => 'admin',
        //     ],
        //     [
        //         'permission_id' => 'ms-ipcr',
        //         'permission_name' => 'IPCR Signatory',
        //         'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","m-signatories")->first()->id,
        //         'create_id' => 'admin',
        //     ],
        // ]);

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

        // DB::table('access_rights')->insert([
        //     [
        //         'permission_id' => 'msa-create',
        //         'permission_name' => 'create',
        //         'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","ms-aapcr")->first()->id,
        //         'create_id' => 'admin',
        //     ],
        //     [
        //         'permission_id' => 'msa-delete',
        //         'permission_name' => 'delete',
        //         'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","ms-aapcr")->first()->id,
        //         'create_id' => 'admin',
        //     ],
        //     [
        //         'permission__id' => 'msovp-create',
        //         'permission_name' => 'create',
        //         'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","ms-opcrvp")->first()->id,
        //         'create_id' => 'admin',
        //     ],
        //     [
        //         'permission_id' => 'msovp-delete',
        //         'permission_name' => 'delete',
        //         'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","ms-opcrvp")->first()->id,
        //         'create_id' => 'admin',
        //     ],
        //     [
        //         'permission_id' => 'mso-create',
        //         'permission_name' => 'create',
        //         'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","ms-opcr")->first()->id,
        //         'create_id' => 'admin',
        //     ],
        //     [
        //         'permission_id' => 'mso-delete',
        //         'permission_name' => 'delete',
        //         'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","ms-opcr")->first()->id,
        //         'create_id' => 'admin',
        //     ],
        //     [
        //         'permission_id' => 'msc-create',
        //         'permission_name' => 'create',
        //         'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","ms-cpcr")->first()->id,
        //         'create_id' => 'admin',
        //     ],
        //     [
        //         'permission_id' => 'msc-delete',
        //         'permission_name' => 'delete',
        //         'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","ms-cpcr")->first()->id,
        //         'create_id' => 'admin',
        //     ],
        //     [
        //         'permission_id' => 'msi-create',
        //         'permission_name' => 'create',
        //         'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","ms-ipcr")->first()->id,
        //         'create_id' => 'admin',
        //     ],
        //     [
        //         'permission_id' => 'msi-delete',
        //         'permission_name' => 'delete',
        //         'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","ms-ipcr")->first()->id,
        //         'create_id' => 'admin',
        //     ],
        // ]);
    }
}
