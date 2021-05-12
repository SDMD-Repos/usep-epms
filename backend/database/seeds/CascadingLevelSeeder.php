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
                'id' => 'vps',
                'name' => 'VPs',
                'ordering' => 1,
                'create_id' => 'admin',
                'history' => 'Created ' . Carbon::now() . " by admin\n"
            ],
            [
                'id' => 'offices',
                'name' => 'Offices',
                'ordering' => 2,
                'create_id' => 'admin',
                'history' => 'Created ' . Carbon::now() . " by admin\n"
            ],
            [
                'id' => 'colleges',
                'name' => 'Colleges',
                'ordering' => 3,
                'create_id' => 'admin',
                'history' => 'Created ' . Carbon::now() . " by admin\n"
            ],
            [
                'id' => 'individuals',
                'name' => 'Individuals',
                'ordering' => 4,
                'create_id' => 'admin',
                'history' => 'Created ' . Carbon::now() . " by admin\n"
            ]
        ]);
    }
}
