<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOfficeTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('office_types', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->string('code', 25);
            $table->string('name', 50);
            $table->timestamp('created_at')->useCurrent();
            $table->string('create_id', 35);
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('office_types');
    }
}
