<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeOpcrTemplateDetailMeasuresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('opcr_template_detail_measures', function (Blueprint $table) {
            $table->integer("id")->autoIncrement();
            $table->integer("detail_id");
            $table->integer("measure_id");
            $table->timestamp('created_at')->useCurrent();
            $table->string('create_id', 35);
            $table->timestamp('updated_at')->nullable();
            $table->string('modify_id', 35)->nullable();
            $table->softDeletes();
            $table->text('history');

            $table->foreign('detail_id')->references('id')->on('opcr_template_details')->onUpdate('cascade');
            $table->foreign('measure_id')->references('id')->on('measures')->onUpdate('cascade');
        });

        Schema::table('opcr_template_detail_measures', function (Blueprint $table) {
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
        Schema::table('opcr_template_detail_measures', function (Blueprint $table) {
            //
        });
    }
}
