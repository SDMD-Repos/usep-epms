<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLinkToChildToOpcrTemplateDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('opcr_template_details', function (Blueprint $table) {
            $table->smallInteger('linked_to_child')->after('target')->default(0);
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
        });
    }
}
