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
        DB::table('access_rights')->insert([
            [
               
                'permission' => 'Manager',
                'create_id' => 'admin',
                'history' => 'Created ' . Carbon::now() . " by admin\n"
            ],
            [
                'permission' => 'Manager',
                'create_id' => 'admin',
                'history' => 'Created ' . Carbon::now() . " by admin\n"
            ],
        ]);
    }
}
