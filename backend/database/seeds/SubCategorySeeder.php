<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('sub_categories')->insert([
            [
                'name' => 'PREXC Indicators',
                'category_id' => 'core_functions',
                'parent_id' => NULL,
                'create_id' => 'admin',
                'history' => 'Created '.Carbon::now()." by admin\n"
            ],
            [
                'name' => 'Outcome Indicators',
                'category_id' => 'core_functions',
                'parent_id' => 1,
                'create_id' => 'admin',
                'history' => 'Created '.Carbon::now()." by admin\n"
            ],
            [
                'name' => 'Output Indicators',
                'category_id' => 'core_functions',
                'parent_id' => 1,
                'create_id' => 'admin',
                'history' => 'Created '.Carbon::now()." by admin\n"
            ],
            [
                'name' => 'Catch-Up Plan Targets',
                'category_id' => 'core_functions',
                'parent_id' => NULL,
                'create_id' => 'admin',
                'history' => 'Created '.Carbon::now()." by admin\n"
            ],
            [
                'name' => 'LOCALLY-FUNDED PROJECTS',
                'category_id' => 'core_functions',
                'parent_id' => NULL,
                'create_id' => 'admin',
                'history' => 'Created '.Carbon::now()." by admin\n"
            ],
            [
                'name' => '2020 PBB compliance',
                'category_id' => 'support_functions',
                'parent_id' => NULL,
                'create_id' => 'admin',
                'history' => 'Created '.Carbon::now()." by admin\n"
            ],
            [
                'name' => 'Financial',
                'category_id' => 'support_functions',
                'parent_id' => NULL,
                'create_id' => 'admin',
                'history' => 'Created '.Carbon::now()." by admin\n"
            ],
            [
                'name' => 'Projects funded under 164',
                'category_id' => 'support_functions',
                'parent_id' => NULL,
                'create_id' => 'admin',
                'history' => 'Created '.Carbon::now()." by admin\n"
            ]
        ]);
    }
}
