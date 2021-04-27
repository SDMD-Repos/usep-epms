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
                'formal_name' => 'Agency Annual Performance Commitment and Review'
            ],
            [
                'id' => 'vpopcr',
                'abbreviation' => "OPCR (VPs)",
                'formal_name' => "Office Performance Commitment and Review (Vice Presidents)"
            ],
            [
                'id' => 'opcr',
                'abbreviation' => "OPCR",
                'formal_name' => "Office Performance Commitment and Review (Directors)"
            ],
            [
                'id' => 'cpcr',
                'abbreviation' => "CPCR",
                'formal_name' => "College Performance Commitment and Review"
            ],
            [
                'id' => 'ipcr',
                'abbreviation' => "IPCR",
                'formal_name' => "Individual Performance Commitment and Review"
            ]
        ]);
    }
}
