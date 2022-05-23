<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFormIdToFormFieldSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('form_field_settings', function (Blueprint $table) {
            $table->string('form_id', 15)->nullable()->after('id');

            $table->foreign('form_id')->references('id')->on('forms')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('form_field_settings', function (Blueprint $table) {
            //
        });
    }
}
