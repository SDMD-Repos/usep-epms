<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAapcrsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('aapcrs', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->year('year');
            $table->string('document_name');
            $table->date('end_effectivity')->nullable();
            $table->dateTime('reviewed_date')->nullable();
            $table->dateTime('approved_date')->nullable();
            $table->dateTime('finalized_date')->nullable();
            $table->dateTime('published_date')->nullable();
            $table->tinyInteger('is_active')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->string('create_id', 35);
            $table->timestamp('updated_at')->nullable();
            $table->string('modify_id', 35)->nullable();
            $table->softDeletes();
            $table->text('history');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('aapcrs');
    }
}
