<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCascadingLevelToVpOpcrDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vp_opcr_details', function (Blueprint $table) {
            $table->string('cascading_level', 25)->after('targets_basis')->nullable();

            $table->foreign('cascading_level')->references('code')->on('cascading_levels')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vp_opcr_details', function (Blueprint $table) {
            //
        });
    }
}
