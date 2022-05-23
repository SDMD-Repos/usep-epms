<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddDocumentNameToFormUnpublishStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('form_unpublish_statuses', function (Blueprint $table) {
            $table->string('document_name', 100)->after('form_id');
            $table->string('requested_by', 75)->after('requested_date');

            $table->dropColumn('verified_date');
            $table->dropColumn('verified_by');

            $table->timestamp('changed_date')->nullable()->after('requested_by');
            $table->string('changed_by', 75)->nullable()->after('changed_date');

            $table->string('file_name', 150)->nullable()->after('changed_by');
        });

        DB::statement("ALTER TABLE form_unpublish_statuses CHANGE COLUMN status status ENUM('pending', 'verified', 'declined', 'cancelled') NOT NULL");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('form_unpublish_statuses', function (Blueprint $table) {
            //
        });
    }
}
