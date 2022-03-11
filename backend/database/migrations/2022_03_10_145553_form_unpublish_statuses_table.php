<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FormUnpublishStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('form_unpublish_statuses', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->string('form_type', 15);
            $table->integer('form_id');
            $table->text('remarks');
            $table->enum('status', ['pending', 'verified']);
            $table->timestamp('requested_date');
            $table->timestamp('verified_date')->nullable();
            $table->string('verified_by', 75)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->string('create_id', 35);
            $table->timestamp('updated_at')->nullable();
            $table->string('modify_id', 35)->nullable();
            $table->softDeletes();
            $table->text('history');

            $table->foreign('form_type')->references('id')->on('forms')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('form_unpublish_statuses');
    }
}
