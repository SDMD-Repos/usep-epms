<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CascadingLevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('cascading_levels')->insert([
            [
                'code' => 'offices',
                'name' => 'Offices',
                'ordering' => 1,
                'create_id' => 'admin',
                'history' => 'Created ' . Carbon::now() . " by admin\n"
            ],
            [
                'code' => 'colleges',
                'name' => 'Colleges',
                'ordering' => 2,
                'create_id' => 'admin',
                'history' => 'Created ' . Carbon::now() . " by admin\n"
            ],
            [
                'code' => 'individuals',
                'name' => 'Individuals',
                'ordering' => 3,
                'create_id' => 'admin',
                'history' => 'Created ' . Carbon::now() . " by admin\n"
            ]
        ]);
    }
}
