<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OtherConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('other_configs')->insert([
            [
                'id' => 'default_date_format',
                'value' => 'MM/DD/YYYY',
                'create_id' => 'admin',
                'history' => 'Created ' . Carbon::now() . " by admin\n"
            ]
        ]);
    }
}
