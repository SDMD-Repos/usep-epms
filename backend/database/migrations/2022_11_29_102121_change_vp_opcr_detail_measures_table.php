<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeVpOpcrDetailMeasuresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vp_opcr_detail_measures', function (Blueprint $table) {
            $table->integer('category_id')->nullable()->after('measure_id');
            $table->uuid('uuid')->after('category_id');

            $table->foreign('category_id')->references('id')->on('measure_categories')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vp_opcr_detail_measures', function (Blueprint $table) {
            //
        });
    }
}
