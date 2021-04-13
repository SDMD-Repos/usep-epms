<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('categories')->insert([
            [
                'id' => 'core_functions',
                'name' => 'Core Functions',
                'percentage' => 80,
                'order' => 1,
                'create_id' => 'admin',
                'history' => 'Created '.Carbon::now()." by admin\n"
            ],
            [
                'id' => 'support_functions',
                'name' => 'Support Functions',
                'percentage' => 20,
                'order' => 2,
                'create_id' => 'admin',
                'history' => 'Created '.Carbon::now()." by admin\n"
            ]
        ]);
    }
}
