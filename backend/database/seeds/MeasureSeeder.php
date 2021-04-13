<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MeasureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('measures')->insert([
            [
                'name' => 'Measure 1a',
                'create_id' => 'admin',
                'history' => 'Created '. Carbon::now()." by admin\n"
            ],
            [
                'name' => 'Measure 1b',
                'create_id' => 'admin',
                'history' => 'Created '. Carbon::now()." by admin\n"
            ],
            [
                'name' => 'Measure 1c',
                'create_id' => 'admin',
                'history' => 'Created '. Carbon::now()." by admin\n"
            ],
            [
                'name' => 'Measure 2a',
                'create_id' => 'admin',
                'history' => 'Created '. Carbon::now()." by admin\n"
            ],
            [
                'name' => 'Measure 2b',
                'create_id' => 'admin',
                'history' => 'Created '. Carbon::now()." by admin\n"
            ],
            [
                'name' => 'Measure 3',
                'create_id' => 'admin',
                'history' => 'Created '. Carbon::now()." by admin\n"
            ]
        ]);
    }
}
