<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDescriptionToMeasuresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('measures', function (Blueprint $table) {
            $table->smallInteger('is_custom')->default(0)->after('display_as_items');
            $table->text('description')->nullable()->after('is_custom');
            $table->string('variable_equivalent', 15)->nullable()->after('description');
            $table->text('elements')->nullable()->after('variable_equivalent');
            $table->string('bg_color', 10)->nullable()->after('elements');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('measures', function (Blueprint $table) {
            //
        });
    }
}
