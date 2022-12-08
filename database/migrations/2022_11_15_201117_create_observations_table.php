<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateObservationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('observations', function (Blueprint $table) {
            $table->id();
            $table->string("description", 255);
            $table->string("photo", 255);
            $table->unsignedBigInteger("person_room_id");
            $table->unsignedBigInteger("status_id");
        });

        Schema::table('observations', function (Blueprint $table) {
            $table->foreign("status_id")->references("id")->on("status");
            $table->foreign("person_room_id")->references("id")->on("person_rooms");
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('observations');
    }
}
