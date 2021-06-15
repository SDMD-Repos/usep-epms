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
                'id' => 'reviewed_by',
                'name' => 'Reviewed by'
            ],
            [
                'id' => 'approved_by',
                'name' => 'Approved by'
            ],
            [
                'id' => 'prepared_by',
                'name' => 'Prepared by'
            ]
        ]);
    }
}
