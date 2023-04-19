<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSubunitIdToVpOpcrDetailOfficesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vp_opcr_detail_offices', function (Blueprint $table) {
            $table->string('subunit_id', 25)->after('office_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vp_opcr_detail_offices', function (Blueprint $table) {
            //
        });
    }
}
