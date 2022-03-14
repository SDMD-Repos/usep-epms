<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProgramIdToOpcrTemplateDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('opcr_template_details', function (Blueprint $table) {
            //
            $table->integer('program_id')->after('opcr_template_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('opcr_template_details', function (Blueprint $table) {
            //
            $table->foreign('program_id')->references('id')->on('programs')->onUpdate('cascade');
        });
    }
}
