<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('positions')->insert([
            [
                'id' => 'reviewed_by',
                'name' => 'Reviewed by'
            ],
            [
                'id' => 'approved_by',
                'name' => 'Approved by'
            ]
        ]);
    }
}
