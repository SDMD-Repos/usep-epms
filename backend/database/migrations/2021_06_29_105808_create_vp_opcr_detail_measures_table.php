<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVpOpcrDetailMeasuresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vp_opcr_detail_measures', function (Blueprint $table) {
            $table->integer("id")->autoIncrement();
            $table->integer("detail_id");
            $table->integer("measure_id");
            $table->timestamp('created_at')->useCurrent();
            $table->string('create_id', 35);
            $table->timestamp('updated_at')->nullable();
            $table->string('modify_id', 35)->nullable();
            $table->softDeletes();
            $table->text('history');

            $table->foreign('detail_id')->references('id')->on('vp_opcr_details')->onUpdate('cascade');
            $table->foreign('measure_id')->references('id')->on('measures')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vp_opcr_detail_measures');
    }
}
