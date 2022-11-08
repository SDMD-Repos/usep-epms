<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeMeasureItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('measure_items', function (Blueprint $table) {
            $table->integer('category_id')->after('measure_id')->nullable();
            $table->renameColumn('rate', 'rating');
            $table->text('description')->change();

            $table->foreign('category_id')->references('id')->on('measure_categories')->onUpdate('cascade');
            $table->foreign('rating')->references('id')->on('measure_ratings')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('measure_items', function (Blueprint $table) {
            //
        });
    }
}
