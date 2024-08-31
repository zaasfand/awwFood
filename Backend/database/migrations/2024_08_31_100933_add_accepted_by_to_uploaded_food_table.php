<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAcceptedByToUploadedFoodTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('uploaded_food', function (Blueprint $table) {
            $table->unsignedBigInteger('accepted_by')->nullable()->after('is_accepted');

            // Add foreign key constraint if you have a related users table
            // $table->foreign('accepted_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('uploaded_food', function (Blueprint $table) {
            $table->dropColumn('accepted_by');
        });
    }
}
