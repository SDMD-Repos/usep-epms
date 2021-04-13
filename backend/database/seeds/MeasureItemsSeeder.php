<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MeasureItemsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Measure 1a
        DB::table('measure_items')->insert([
            [
                'measure_id' => 1,
                'rate' => 5,
                'description' => '100%',
                'create_id' => 'admin',
                'history' => 'Created '.Carbon::now()." by admin\n"
            ],
            [
                'measure_id' => 1,
                'rate' => 4,
                'description' => '95%-99%',
                'create_id' => 'admin',
                'history' => 'Created '.Carbon::now()." by admin\n"
            ],
            [
                'measure_id' => 1,
                'rate' => 3,
                'description' => '90%-94%',
                'create_id' => 'admin',
                'history' => 'Created '.Carbon::now()." by admin\n"
            ],
            [
                'measure_id' => 1,
                'rate' => 2,
                'description' => '85%-89%',
                'create_id' => 'admin',
                'history' => 'Created '.Carbon::now()." by admin\n"
            ],
            [
                'measure_id' => 1,
                'rate' => 1,
                'description' => 'Below 85%',
                'create_id' => 'admin',
                'history' => 'Created '.Carbon::now()." by admin\n"
            ]
        ]);

        // Measure 1b
        DB::table('measure_items')->insert([
            [
                'measure_id' => 2,
                'rate' => 5,
                'description' => 'Accurate/No revision/error',
                'create_id' => 'admin',
                'history' => 'Created '.Carbon::now()." by admin\n"
            ],
            [
                'measure_id' => 2,
                'rate' => 4,
                'description' => '1%-5% revisions/errors',
                'create_id' => 'admin',
                'history' => 'Created '.Carbon::now()." by admin\n"
            ],
            [
                'measure_id' => 2,
                'rate' => 3,
                'description' => '6%-9% revisions/errors',
                'create_id' => 'admin',
                'history' => 'Created '.Carbon::now()." by admin\n"
            ],
            [
                'measure_id' => 2,
                'rate' => 2,
                'description' => '10%-15% revisions/errors',
                'create_id' => 'admin',
                'history' => 'Created '.Carbon::now()." by admin\n"
            ],
            [
                'measure_id' => 2,
                'rate' => 1,
                'description' => 'Above 15% revisions/errors',
                'create_id' => 'admin',
                'history' => 'Created '.Carbon::now()." by admin\n"
            ]
        ]);

        // Measure 1c
        DB::table('measure_items')->insert([
            [
                'measure_id' => 3,
                'rate' => 5,
                'description' => 'Accurate/No revision/lapse',
                'create_id' => 'admin',
                'history' => 'Created '.Carbon::now()." by admin\n"
            ],
            [
                'measure_id' => 3,
                'rate' => 4,
                'description' => '1-2 errors/lapses',
                'create_id' => 'admin',
                'history' => 'Created '.Carbon::now()." by admin\n"
            ],
            [
                'measure_id' => 3,
                'rate' => 3,
                'description' => '3-4 errors/lapses',
                'create_id' => 'admin',
                'history' => 'Created '.Carbon::now()." by admin\n"
            ],
            [
                'measure_id' => 3,
                'rate' => 2,
                'description' => '5-6 errors/lapses',
                'create_id' => 'admin',
                'history' => 'Created '.Carbon::now()." by admin\n"
            ],
            [
                'measure_id' => 3,
                'rate' => 1,
                'description' => 'More than 6 errors/lapses',
                'create_id' => 'admin',
                'history' => 'Created '.Carbon::now()." by admin\n"
            ]
        ]);

        // Measure 2a
        DB::table('measure_items')->insert([
            [
                'measure_id' => 4,
                'rate' => 5,
                'description' => 'Exceeding targets by 30% and above of the planned targets (130% and up)',
                'create_id' => 'admin',
                'history' => 'Created '.Carbon::now()." by admin\n"
            ],
            [
                'measure_id' => 4,
                'rate' => 4,
                'description' => 'Exceeding targets by 15% to 29% of the planned targets (115%-120%)',
                'create_id' => 'admin',
                'history' => 'Created '.Carbon::now()." by admin\n"
            ],
            [
                'measure_id' => 4,
                'rate' => 3,
                'description' => 'Performance of 100% to 114% of the planned targets (100%-114%)',
                'create_id' => 'admin',
                'history' => 'Created '.Carbon::now()." by admin\n"
            ],
            [
                'measure_id' => 4,
                'rate' => 2,
                'description' => 'Performance of 51% to 99% of the planned targets (51%-99%)',
                'create_id' => 'admin',
                'history' => 'Created '.Carbon::now()." by admin\n"
            ],
            [
                'measure_id' => 4,
                'rate' => 1,
                'description' => 'Performance failing to meet the planned targets by 50% or below (50% and below)',
                'create_id' => 'admin',
                'history' => 'Created '.Carbon::now()." by admin\n"
            ]
        ]);

        // Measure 2b
        DB::table('measure_items')->insert([
            [
                'measure_id' => 5,
                'rate' => 5,
                'description' => '100%',
                'create_id' => 'admin',
                'history' => 'Created '.Carbon::now()." by admin\n"
            ],
            [
                'measure_id' => 5,
                'rate' => 4,
                'description' => '95%-99%',
                'create_id' => 'admin',
                'history' => 'Created '.Carbon::now()." by admin\n"
            ],
            [
                'measure_id' => 5,
                'rate' => 3,
                'description' => '90%-94%',
                'create_id' => 'admin',
                'history' => 'Created '.Carbon::now()." by admin\n"
            ],
            [
                'measure_id' => 5,
                'rate' => 2,
                'description' => '85%-89%',
                'create_id' => 'admin',
                'history' => 'Created '.Carbon::now()." by admin\n"
            ],
            [
                'measure_id' => 5,
                'rate' => 1,
                'description' => 'Below 85%',
                'create_id' => 'admin',
                'history' => 'Created '.Carbon::now()." by admin\n"
            ]
        ]);

        // Measure 3
        DB::table('measure_items')->insert([
            [
                'measure_id' => 6,
                'rate' => 5,
                'description' => 'Before due date',
                'create_id' => 'admin',
                'history' => 'Created '.Carbon::now()." by admin\n"
            ],
            [
                'measure_id' => 6,
                'rate' => 4,
                'description' => 'On due date',
                'create_id' => 'admin',
                'history' => 'Created '.Carbon::now()." by admin\n"
            ],
            [
                'measure_id' => 6,
                'rate' => 3,
                'description' => '1 day after due',
                'create_id' => 'admin',
                'history' => 'Created '.Carbon::now()." by admin\n"
            ],
            [
                'measure_id' => 6,
                'rate' => 2,
                'description' => '2 days after due',
                'create_id' => 'admin',
                'history' => 'Created '.Carbon::now()." by admin\n"
            ],
            [
                'measure_id' => 6,
                'rate' => 1,
                'description' => '3-5 days after due',
                'create_id' => 'admin',
                'history' => 'Created '.Carbon::now()." by admin\n"
            ]
        ]);
    }
}
