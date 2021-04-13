<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProgramSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('programs')->insert([
            [
                'name' => 'Higher Education Program',
                'category_id' => 'core_functions',
                'percentage' => 25,
                'create_id' => 'admin',
                'history' => 'Created '.Carbon::now()." by admin\n"
            ],
            [
                'name' => 'Advanced Education Program',
                'category_id' => 'core_functions',
                'percentage' => 25,
                'create_id' => 'admin',
                'history' => 'Created '.Carbon::now()." by admin\n"
            ],
            [
                'name' => 'Research Program',
                'category_id' => 'core_functions',
                'percentage' => 25,
                'create_id' => 'admin',
                'history' => 'Created '.Carbon::now()." by admin\n"
            ],
            [
                'name' => 'Technical Advisory and Extension Program',
                'category_id' => 'core_functions',
                'percentage' => 25,
                'create_id' => 'admin',
                'history' => 'Created '.Carbon::now()." by admin\n"
            ],
            [
                'name' => 'Support to Operations',
                'category_id' => 'support_functions',
                'percentage' => 30,
                'create_id' => 'admin',
                'history' => 'Created '.Carbon::now()." by admin\n"
            ],
            [
                'name' => 'General Administration and Support',
                'category_id' => 'support_functions',
                'percentage' => 70,
                'create_id' => 'admin',
                'history' => 'Created '.Carbon::now()." by admin\n"
            ]
        ]);
    }
}
