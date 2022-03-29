<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateCascadeToToVpOpcrDetailOffices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vp_opcr_detail_offices', function (Blueprint $table) {
            //
            $table->dropForeign(['cascade_to']);

            $table->foreign('cascade_to')
                ->references('id')
                ->on('programs')
                ->onDelete('cascade');
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
