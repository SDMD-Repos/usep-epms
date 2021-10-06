<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGroupsToVpOpcrDetailOffices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vp_opcr_detail_offices', function (Blueprint $table) {
            $table->smallInteger('is_group')->default(0)->after('office_name')->nullable();
            $table->integer('group_id')->after('is_group')->nullable();

            $table->string('office_id', 25)->nullable()->change();
            $table->string('office_name', 50)->nullable()->change();

            $table->foreign('group_id')->references('id')->on('groups')->onUpdate('cascade');
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
