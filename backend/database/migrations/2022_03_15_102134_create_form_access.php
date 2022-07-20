<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFormAccess extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('form_access', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->string('form_id', 15);
            $table->string('pmaps_id')->nullable();
            $table->string('pmaps_name', 100);
            $table->string('office_id', 15)->nullable();
            $table->string('office_name', 100)->nullable();
            $table->string('staff_id')->nullable();
            $table->string('staff_name', 100)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->string('create_id', 35);
            $table->timestamp('updated_at')->nullable();
            $table->string('modify_id', 35)->nullable();
            $table->softDeletes();


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
        Schema::dropIfExists('form_access');
    }
}
