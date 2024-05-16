<?php
namespace Database\Seeders;
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
                'name' => 'Reviewed by',
                'ordering' => 2
            ],
            [
                'code' => 'approved_by',
                'name' => 'Approved by',
                'ordering' => 3
            ],
            [
                'code' => 'prepared_by',
                'name' => 'Prepared by',
                'ordering' => 1
            ],
            [
                'code' => 'assessed_by',
                'name' => 'Assessed by',
                'ordering' => 4
            ]
        ]);
    }
}
