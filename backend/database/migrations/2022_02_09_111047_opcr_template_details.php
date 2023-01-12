<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class OpcrTemplateDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('opcr_template_details', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->integer('opcr_template_id');
            $table->integer('parent_id')->nullable();
            $table->integer('category_id');
            $table->integer('sub_category_id')->nullable();
            $table->text('pi_name');
            $table->smallInteger('is_header')->default(0)->nullable();
            $table->string('target')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->string('create_id', 35);
            $table->timestamp('updated_at')->nullable();
            $table->string('modify_id', 35)->nullable();
            $table->softDeletes();
            $table->text('history');

            $table->foreign('opcr_template_id')->references('id')->on('opcr_templates')->onUpdate('cascade');
            $table->foreign('category_id')->references('id')->on('categories')->onUpdate('cascade');
            $table->foreign('sub_category_id')->references('id')->on('sub_categories')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::dropIfExists('opcr_template_details');
    }
}
