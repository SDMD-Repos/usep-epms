<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMeasureRatingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('measure_ratings', function (Blueprint $table) {
            $table->integer("id")->autoIncrement();
            $table->year('year');
            $table->smallInteger("numerical_rating");
            $table->decimal("aps_from", 4, 2);
            $table->decimal("aps_to", 4, 2);
            $table->string("adjectival_rating", 75);
            $table->text("description");

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
        Schema::dropIfExists('measure_ratings');
    }
}
