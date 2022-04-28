<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOtherProgramsToVpOpcrDetailOfficesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vp_opcr_detail_offices', function (Blueprint $table) {
            $table->dropForeign('vp_opcr_detail_offices_cascade_to_foreign');
            $table->dropIndex('vp_opcr_detail_offices_cascade_to_foreign');
            $table->dropColumn('cascade_to');

            $table->integer('category_id')->nullable()->after('office_type_id');
            $table->integer('program_id')->nullable()->after('category_id');
            $table->integer('other_program_id')->nullable()->after('program_id');

            $table->foreign('category_id')->references('id')->on('categories')->onUpdate('cascade');
            $table->foreign('program_id')->references('id')->on('programs')->onUpdate('cascade');
            $table->foreign('other_program_id')->references('id')->on('other_programs')->onUpdate('cascade');
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
