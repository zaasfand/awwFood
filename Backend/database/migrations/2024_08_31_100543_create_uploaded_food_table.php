<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUploadedFoodTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('uploaded_food', function (Blueprint $table) {
            $table->id(); // Adds an auto-incrementing ID column
            $table->unsignedBigInteger('uploaded_by'); // Foreign key to users or other table
            $table->text('food_items'); // Assuming it's a text field; adjust as needed
            $table->string('image'); // Path or URL to the image
            $table->text('description'); // Description of the food
            $table->string('location'); // Location where the food is available
            $table->boolean('is_accepted')->default(false); // Whether the food is accepted or not
            $table->unsignedBigInteger('accepted_by')->nullable()->after('is_accepted');
            $table->timestamps(); // Adds created_at and updated_at columns

            // Add foreign key constraint if you have a related users table
            // $table->foreign('uploaded_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('uploaded_food');
    }
}
