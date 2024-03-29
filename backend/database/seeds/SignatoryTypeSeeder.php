<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SignatoryTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('signatory_types')->insert([
            [
                'code' => 'reviewed_by',
                'name' => 'Reviewed by'
            ],
            [
                'code' => 'approved_by',
                'name' => 'Approved by'
            ],
            [
                'code' => 'prepared_by',
                'name' => 'Prepared by'
            ]
        ]);
    }
}
