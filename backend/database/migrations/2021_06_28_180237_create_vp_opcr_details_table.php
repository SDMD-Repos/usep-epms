<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVpOpcrDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vp_opcr_details', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->integer('vp_opcr_id');
            $table->integer('aapcr_detail_id')->nullable();
            $table->smallInteger('is_header')->default(0)->nullable();
            $table->text('pi_name');
            $table->string('target')->nullable();
            $table->float('allocated_budget', 11, 2)->nullable();
            $table->string('targets_basis', 100)->nullable();
            $table->string('category_id', 25);
            $table->integer('sub_category_id')->nullable();
            $table->integer('program_id')->nullable();
            $table->text('remarks')->nullable();
            $table->integer('parent_id')->nullable();
            $table->tinyInteger('from_aapcr')->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->string('create_id', 35);
            $table->timestamp('updated_at')->nullable();
            $table->string('modify_id', 35)->nullable();
            $table->softDeletes();
            $table->text('history');

            $table->foreign('aapcr_detail_id')->references('id')->on('aapcr_details')->onUpdate('cascade');
            $table->foreign('vp_opcr_id')->references('id')->on('vp_opcrs')->onUpdate('cascade');
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
        Schema::dropIfExists('vp_opcr_details');
    }
}
