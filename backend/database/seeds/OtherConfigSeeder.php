<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;

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
                'id' => 'college_vp_id',
                'value' => 31,
                'create_id' => 'admin',
                'history' => 'Created ' . Carbon::now() . " by admin\n"
            ],
        ]);
    }
}
