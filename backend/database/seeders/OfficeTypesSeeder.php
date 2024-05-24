<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OfficeTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('office_types')->insert([
            [
                'code' => 'implementing',
                'name' => 'Implementing Office',
                'create_id' => 'admin'
            ],
            [
                'code' => 'supporting',
                'name' => 'Support Office',
                'create_id' => 'admin'
            ]
        ]);
    }
}
