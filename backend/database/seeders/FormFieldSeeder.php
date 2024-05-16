<?php
namespace Database\Seeders;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FormFieldSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('form_fields')->insert([
            [
                'code' => 'implementing',
                'field_name' => 'Implementing Offices',
                'create_id' => 'admin',
                'history' => 'Created ' . Carbon::now() . " by admin\n"
            ],
            [
                'code' => 'supporting',
                'field_name' => 'Supporting Offices',
                'create_id' => 'admin',
                'history' => 'Created ' . Carbon::now() . " by admin\n"
            ],
        ]);
    }
}
