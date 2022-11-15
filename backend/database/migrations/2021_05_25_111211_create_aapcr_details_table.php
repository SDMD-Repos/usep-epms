<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAapcrDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('aapcr_details', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->integer('aapcr_id');
            $table->smallInteger('is_header')->default(0)->nullable();
            $table->text('pi_name');
            $table->string('target')->nullable();
            $table->float('allocated_budget', 11, 2)->nullable();
            $table->string('targets_basis')->nullable();
            $table->enum('cascading_level', ['offices', 'colleges', 'offices_colleges', 'individuals'])->nullable();
            $table->integer('category_id');
            $table->integer('sub_category_id')->nullable();
            $table->integer('program_id');
            $table->text('other_remarks')->nullable();
            $table->integer('parent_id')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->string('create_id', 35);
            $table->timestamp('updated_at')->nullable();
            $table->string('modify_id', 35)->nullable();
            $table->softDeletes();
            $table->text('history');

            $table->foreign('aapcr_id')->references('id')->on('aapcrs')->onUpdate('cascade');
            $table->foreign('category_id')->references('id')->on('categories')->onUpdate('cascade');
            $table->foreign('sub_category_id')->references('id')->on('sub_categories')->onUpdate('cascade');
            $table->foreign('program_id')->references('id')->on('programs')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('aapcr_details');
    }
}
