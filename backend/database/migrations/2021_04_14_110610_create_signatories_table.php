<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSignatoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('signatories', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->year('year');
            $table->integer('type_id');
            $table->string('form_id', 15);
            $table->string('personnel_id', 30)->nullable();
            $table->string('personnel_name', 150);
            $table->string('office_id', 30)->nullable();
            $table->string('office_name', 150);
            $table->string('position', 150)->nullable();
            $table->string('office_form_id', 10)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->string('create_id', 35);
            $table->timestamp('updated_at')->nullable();
            $table->string('modify_id', 35)->nullable();
            $table->softDeletes();
            $table->text('history');

            $table->foreign('type_id')->references('id')->on('signatory_types')->onUpdate('cascade');
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
        Schema::dropIfExists('signatories');
    }
}
