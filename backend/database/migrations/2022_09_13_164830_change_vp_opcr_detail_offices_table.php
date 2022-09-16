<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeVpOpcrDetailOfficesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vp_opcr_detail_offices', function (Blueprint $table) {
            $table->dropForeign('vp_opcr_detail_offices_other_program_id_foreign');
            $table->dropIndex('vp_opcr_detail_offices_other_program_id_foreign');
            $table->dropColumn('other_program_id');
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
