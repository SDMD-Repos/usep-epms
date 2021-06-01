<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAapcrProgramBudgetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('aapcr_program_budgets', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->integer('aapcr_id');
            $table->integer('program_id');
            $table->float('budget', 12, 0);
            $table->timestamp('created_at')->useCurrent();
            $table->string('create_id', 35);
            $table->timestamp('updated_at')->nullable();
            $table->string('modify_id', 35)->nullable();
            $table->softDeletes();
            $table->text('history');

            $table->foreign('aapcr_id')->references('id')->on('aapcrs')->onUpdate('cascade');
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
        Schema::dropIfExists('aapcr_program_budgets');
    }
}
