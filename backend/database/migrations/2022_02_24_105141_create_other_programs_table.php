<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOtherProgramsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('other_programs', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->string('name', 100);
            $table->string('form_id');
            $table->integer('percentage')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->string('create_id', 35);
            $table->timestamp('updated_at')->nullable();
            $table->string('modify_id', 35)->nullable();
            $table->softDeletes();
            $table->text('history');

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
        Schema::dropIfExists('other_programs');
    }
}
