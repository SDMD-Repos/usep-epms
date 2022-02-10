<?php

use Illuminate\Database\Seeder;

class AccessRightsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('access_rights')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        DB::table('access_rights')->insert([
            [
               
                'permission_name' => 'Manager',
                'permission_id' => 'manager',
                'create_id' => 'admin',
            ],
            
            [
               
                'permission_name' => 'APPCR',
                'permission_id' => 'aapcr',
                'create_id' => 'admin',
            ],
            [
               
                'permission_name' => 'OPCR(VP)',
                'permission_id' => 'opcrvp',
                'create_id' => 'admin',
            ],
            [
               
                'permission_name' => 'OPCR',
                'permission_id' => 'opcr',
                'create_id' => 'admin',
            ],
            [
               
                'permission_id' => 'cpcr',
                'permission_name' => 'CPCR',
                'create_id' => 'admin',
            ],
            [
               
                'permission_id' => 'ipcr',
                'permission_name' => 'IPCR',
                'create_id' => 'admin',
            ],

        ]);

        

        DB::table('access_rights')->insert([
           
            [
                'permission_name' => 'Group',
                'permission_id' => 'm-group',
                'create_id' => 'admin',
                'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","manager")->first()->id,
            ],
            [
                'permission_name' => 'Measures',
                'permission_id' => 'm-measures',
                'create_id' => 'admin',
                'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","manager")->first()->id,
            ],
            [
                'permission_name' => 'Signatories',
                'permission_id' => 'm-signatories',
                'create_id' => 'admin',
                'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","manager")->first()->id,
            ],
             [
                'permission_name' => 'Form',
                'permission_id' => 'm-form',
                'create_id' => 'admin',
                'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","manager")->first()->id,
            ],
        ]);

        DB::table('access_rights')->insert([
            [
                'permission_name' => 'Functions',
                'permission_id' => 'mf-functions',
                'create_id' => 'admin',
                'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","m-form")->first()->id,
            ],
            [
                'permission_name' => 'Programs',
                'permission_id' => 'mf-programs',
                'create_id' => 'admin',
                'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","m-form")->first()->id,
            ],
            [
                'permission_name' => 'Sub Categories',
                'permission_id' => 'mf-subcat',
                'create_id' => 'admin',
                'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","m-form")->first()->id,
            ],
          
            
        ]);

        DB::table('access_rights')->insert([
            [
                'permission_name' => 'AAPCR Signatory',
                'permission_id' => 'ms-aapcr',
                'create_id' => 'admin',
                'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","m-signatories")->first()->id,
            ],
            [
                'permission_name' => 'OPCR (VPs) Signatory',
                'permission_id' => 'ms-opcrvp',
                'create_id' => 'admin',
                'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","m-signatories")->first()->id,
            ],
            [
                'permission_name' => 'OPCR Signatory',
                'permission_id' => 'ms-opcr',
                'create_id' => 'admin',
                'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","m-signatories")->first()->id,
            ],
            [
                'permission_name' => 'CPCR Signatory',
                'permission_id' => 'ms-cpcr',
                'create_id' => 'admin',
                'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","m-signatories")->first()->id,
            ],
            [
                'permission_name' => 'IPCR Signatory',
                'permission_id' => 'ms-ipcr',
                'create_id' => 'admin',
                'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","m-signatories")->first()->id,
            ],
          
            
        ]);

        DB::table('access_rights')->insert([
            [
                'permission_name' => 'create',
                'permission_id' => 'mff-create',
                'create_id' => 'admin',
                'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","mf-functions")->first()->id,
            ],
            [
                'permission_name' => 'delete',
                'permission_id' => 'mff-delete',
                'create_id' => 'admin',
                'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","mf-functions")->first()->id,
            ],
            [
                'permission_name' => 'create',
                'permission_id' => 'mg-create',
                'create_id' => 'admin',
                'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","m-group")->first()->id,
            ],
            [
                'permission_name' => 'delete',
                'permission_id' => 'mg-delete',
                'create_id' => 'admin',
                'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","m-group")->first()->id,
            ],
            [
                'permission_name' => 'create',
                'permission_id' => 'mm-create',
                'create_id' => 'admin',
                'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","m-measures")->first()->id,
            ],
            [
                'permission_name' => 'delete',
                'permission_id' => 'mm-delete',
                'create_id' => 'admin',
                'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","m-measures")->first()->id,
            ],

        ]);

        DB::table('access_rights')->insert([
            [
                'permission_name' => 'create',
                'permission_id' => 'mfp-create',
                'create_id' => 'admin',
                'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","mf-programs")->first()->id,
            ],
            [
                'permission_name' => 'delete',
                'permission_id' => 'mfp-delete',
                'create_id' => 'admin',
                'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","mf-programs")->first()->id,
            ],
        ]);

        DB::table('access_rights')->insert([
            [
                'permission_name' => 'create',
                'permission_id' => 'mfs-create',
                'create_id' => 'admin',
                'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","mf-subcat")->first()->id,
            ],
            [
                'permission_name' => 'delete',
                'permission_id' => 'mfs-delete',
                'create_id' => 'admin',
                'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","mf-subcat")->first()->id,
            ],
            
        ]);

        DB::table('access_rights')->insert([
            [
                'permission_name' => 'create',
                'permission_id' => 'msa-create',
                'create_id' => 'admin',
                'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","ms-aapcr")->first()->id,
            ],
            [
                'permission_id' => 'msa-delete',
                'permission_name' => 'delete',
                'create_id' => 'admin',
                'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","ms-aapcr")->first()->id,
            ],
            [
                'permission__id' => 'msovp-create',
                'permission_name' => 'create',
                'create_id' => 'admin',
                'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","ms-opcrvp")->first()->id,
            ],
            [
                'permission_id' => 'msovp-delete',
                'permission_name' => 'delete',
                'create_id' => 'admin',
                'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","ms-opcrvp")->first()->id,
            ],
            [
                'permission_id' => 'mso-create',
                'permission_name' => 'create',
                'create_id' => 'admin',
                'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","ms-opcr")->first()->id,
            ],
            [
                'permission_id' => 'mso-delete',
                'permission_name' => 'delete',
                'create_id' => 'admin',
                'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","ms-opcr")->first()->id,
            ],
            [
                'permission_id' => 'msc-create',
                'permission_name' => 'create',
                'create_id' => 'admin',
                'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","ms-cpcr")->first()->id,
            ],
            [
                'permission_name' => 'msc-delete',
                'create_id' => 'admin',
                'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","ms-cpcr")->first()->id,
            ],
            [
                'permission_name' => 'msi-create',
                'create_id' => 'admin',
                'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","ms-ipcr")->first()->id,
            ],
            [
                'permission_name' => 'msi-delete',
                'create_id' => 'admin',
                'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_id","ms-ipcr")->first()->id,
            ], 
        ]);

        DB::table('access_rights')->insert([
            [
                'permission_name' => 'create',
                'create_id' => 'admin',
                'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_name","Sub Categories")->first()->id,
            ],
            [
                'permission_name' => 'delete',
                'create_id' => 'admin',
                'parent_id' =>  DB::table("access_rights")->select("id")->where("permission_name","Sub Categories")->first()->id,
            ],
            
        ]);


    }
}
