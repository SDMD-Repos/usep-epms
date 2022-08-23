<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FormSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('forms')->insert([
            [
                'id' => 'aapcr',
                'abbreviation' => 'AAPCR',
                'formal_name' => 'Agency Annual Performance Commitment and Review',
                'ordering' => 1
            ],
            [
                'id' => 'vpopcr',
                'abbreviation' => "OPCR (VP)",
                'formal_name' => "Office Performance Commitment and Review (Vice Presidents)",
                'ordering' => 2
            ],
            [
                'id' => 'opcr',
                'abbreviation' => "OPCR",
                'formal_name' => "Office Performance Commitment and Review (Directors)",
                'ordering' => 3
            ],
            [
                'id' => 'cpcr',
                'abbreviation' => "CPCR",
                'formal_name' => "College Performance Commitment and Review",
                'ordering' => 4
            ],
            [
                'id' => 'ipcr',
                'abbreviation' => "IPCR",
                'formal_name' => "Individual Performance Commitment and Review",
                'ordering' => 5
            ]
        ]);
    }
}
