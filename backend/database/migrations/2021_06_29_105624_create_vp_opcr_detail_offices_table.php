<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVpOpcrDetailOfficesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vp_opcr_detail_offices', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->integer('detail_id');
            $table->integer('office_type_id');
            $table->integer('cascade_to');
            $table->string('vp_office_id', 15)->nullable();
            $table->string('office_id',25);
            $table->string('office_name',50);
            $table->timestamp('created_at')->useCurrent();
            $table->string('create_id', 35);
            $table->timestamp('updated_at')->nullable();
            $table->string('modify_id', 35)->nullable();
            $table->softDeletes();
            $table->text('history');

            $table->foreign('detail_id')->references('id')->on('vp_opcr_details')->onUpdate('cascade');
            $table->foreign('office_type_id')->references('id')->on('office_types')->onUpdate('cascade');
            $table->foreign('cascade_to')->references('id')->on('categories')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vp_opcr_detail_offices');
    }
}
